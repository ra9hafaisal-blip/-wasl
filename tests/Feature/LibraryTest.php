<?php

use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createCard(User $owner, array $attributes = []): Quote
{
    return $owner->quotes()->create(array_merge([
        'kind' => 'quote',
        'text' => 'A saved thought worth keeping.',
        'source' => 'A book',
        'note' => null,
        'category' => 'mathematics',
        'tags' => [],
        'visibility' => 'public',
    ], $attributes));
}

test('visitors can browse public cards and published dates without signing in', function () {
    $owner = User::factory()->create(['name' => 'Rahaf Al-Osaimi']);
    $card = createCard($owner);

    $this->get('/')
        ->assertOk()
        ->assertSee('A saved thought worth keeping.')
        ->assertSee('Rahaf Al-Osaimi')
        ->assertSee($card->created_at->format('Y-m-d'));
});

test('registration validates the requested name and password lengths and hashes passwords', function () {
    $response = $this->post('/signup', [
        'name' => 'R',
        'email' => 'short@example.test',
        'password' => '1234567',
        'password_confirmation' => '1234567',
    ]);
    $response->assertSessionHasErrors(['name', 'password']);

    $this->post('/signup', [
        'name' => 'Renad Al-Harbi',
        'email' => 'renad@example.test',
        'password' => 'safe-password-1',
        'password_confirmation' => 'safe-password-1',
    ])->assertRedirect('/');

    $user = User::where('email', 'renad@example.test')->firstOrFail();
    expect(Hash::check('safe-password-1', $user->password))->toBeTrue();
});

test('signed-in members can create a quote within the 155 character requirement', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post('/quotes', [
        'kind' => 'quote',
        'text' => str_repeat('x', 156),
        'category' => 'physics',
        'visibility' => 'public',
    ])->assertSessionHasErrors('text');

    $this->actingAs($user)->post('/quotes', [
        'kind' => 'quote',
        'text' => 'A valid quote.',
        'source' => 'Research notes',
        'category' => 'physics',
        'visibility' => 'public',
    ])->assertRedirect('/');

    $this->assertDatabaseHas('quotes', [
        'user_id' => $user->id,
        'text' => 'A valid quote.',
        'category' => 'physics',
    ]);
});

test('private cards are visible only to their owner', function () {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();
    $privateCard = createCard($owner, ['text' => 'A private notebook entry.', 'visibility' => 'private']);

    $this->get('/')->assertDontSee('A private notebook entry.');
    $this->actingAs($visitor)->get('/')->assertDontSee('A private notebook entry.');
    $this->actingAs($owner)->get('/?mine=1')->assertSee('A private notebook entry.');
    $this->actingAs($visitor)->put('/quotes/'.$privateCard->id, [
        'kind' => 'quote', 'text' => 'Changed by another member', 'category' => 'physics', 'visibility' => 'public',
    ])->assertForbidden();
});

test('only the owner can update and delete a card', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $card = createCard($owner);

    $this->actingAs($other)->put('/quotes/'.$card->id, [
        'kind' => 'quote', 'text' => 'Changed by another member', 'category' => 'physics', 'visibility' => 'public',
    ])->assertForbidden();
    $this->actingAs($other)->delete('/quotes/'.$card->id)->assertForbidden();

    $this->actingAs($owner)->put('/quotes/'.$card->id, [
        'kind' => 'quote', 'text' => 'An updated quote.', 'category' => 'physics', 'visibility' => 'public',
    ])->assertRedirect('/');
    $this->assertDatabaseHas('quotes', ['id' => $card->id, 'text' => 'An updated quote.']);

    $this->actingAs($owner)->delete('/quotes/'.$card->id)->assertRedirect('/');
    $this->assertDatabaseMissing('quotes', ['id' => $card->id]);
});

test('the library paginates public records in groups of twelve and keeps private records out', function () {
    $owner = User::factory()->create();
    foreach (range(1, 13) as $number) {
        createCard($owner, ['text' => 'Public thought number '.$number.'.']);
    }
    createCard($owner, ['text' => 'Private thought hidden from guests.', 'visibility' => 'private']);

    $this->get('/')->assertOk()->assertSee('13 بطاقة')->assertDontSee('Private thought hidden from guests.');
    $this->get('/?page=2')->assertOk()->assertSee('13 بطاقة');
});

test('the idea network page renders saved cards and real link counts', function () {
    $owner = User::factory()->create();
    $first = createCard($owner, ['kind' => 'idea', 'text' => 'Ideas connect across disciplines.']);
    $second = createCard($owner, ['kind' => 'idea', 'text' => 'Shared links make knowledge useful.', 'category' => 'programming']);
    $first->relatedQuotes()->attach($second->id, ['relation_type' => 'supports']);

    $this->get('/network')
        ->assertOk()
        ->assertSee('Ideas connect across disciplines.')
        ->assertSee('Shared links make knowledge useful.')
        ->assertSee('روابط');
});

test('members can filter the library and toggle likes and reposts', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $physicsCard = createCard($owner, ['text' => 'Physics is full of useful questions.', 'category' => 'physics']);
    createCard($owner, ['text' => 'Mathematics is another field.', 'category' => 'mathematics']);

    $this->get('/?category=physics&kind=quote')
        ->assertOk()
        ->assertSee('Physics is full of useful questions.')
        ->assertDontSee('Mathematics is another field.');

    $this->actingAs($member)->post('/quotes/'.$physicsCard->id.'/like')->assertRedirect();
    $this->assertDatabaseHas('quote_likes', ['quote_id' => $physicsCard->id, 'user_id' => $member->id]);
    $this->post('/quotes/'.$physicsCard->id.'/repost')->assertRedirect();
    $this->assertDatabaseHas('quote_reposts', ['quote_id' => $physicsCard->id, 'user_id' => $member->id]);
    $this->post('/quotes/'.$physicsCard->id.'/like')->assertRedirect();
    $this->assertDatabaseMissing('quote_likes', ['quote_id' => $physicsCard->id, 'user_id' => $member->id]);
});

test('members can log in and out', function () {
    $user = User::factory()->create(['email' => 'member@example.test', 'password' => bcrypt('correct-horse-1')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'correct-horse-1'])->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
    $this->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});
