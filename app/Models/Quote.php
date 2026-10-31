<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quote extends Model
{
    protected $fillable = ['text', 'source', 'note', 'category', 'kind', 'tags', 'visibility'];

    protected function casts(): array
    {
        return ['tags' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'quote_likes')->withTimestamps();
    }

    public function reposts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'quote_reposts')->withTimestamps();
    }

    public function relatedQuotes(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'quote_links', 'quote_id', 'related_quote_id')
            ->withPivot('relation_type')
            ->withTimestamps();
    }

    public function relatedFrom(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'quote_links', 'related_quote_id', 'quote_id')
            ->withPivot('relation_type')
            ->withTimestamps();
    }
}
