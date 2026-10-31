<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuoteController extends Controller
{
    private const CATEGORIES = [
        'mathematics' => 'الرياضيات',
        'physics' => 'الفيزياء',
        'biology' => 'الأحياء',
        'psychology' => 'علم النفس',
        'business' => 'إدارة الأعمال',
        'programming' => 'البرمجة',
        'education' => 'التعليم',
        'other' => 'أخرى',
    ];

    public function index(Request $request): View
    {
        $userId = $request->user()?->id;
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $kind = in_array($request->query('kind'), ['quote', 'idea', 'note'], true) ? $request->query('kind') : null;
        $category = in_array($request->query('category'), array_keys(self::CATEGORIES), true) ? $request->query('category') : null;

        $quotes = $this->visibleQuotes($userId)
            ->when($request->boolean('mine') && $userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->with([
                'user',
                'relatedQuotes' => function ($relation) use ($userId): void {
                    $this->applyVisibility($relation, $userId);
                    $relation->with('user');
                },
            ])
            ->withCount(['likes', 'reposts'])
            ->when($userId !== null, fn ($query) => $query->withExists([
                'likes as liked_by_me' => fn ($likes) => $likes->where('users.id', $userId),
                'reposts as reposted_by_me' => fn ($reposts) => $reposts->where('users.id', $userId),
            ]))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($quoteQuery) use ($search): void {
                    $quoteQuery->where('text', 'like', "%{$search}%")
                        ->orWhere('source', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('tags', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($kind !== null, fn ($query) => $query->where('kind', $kind))
            ->when($category !== null, fn ($query) => $query->where('category', $category))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $editingQuoteId = $request->integer('edit');
        $editingQuote = $editingQuoteId > 0 ? $this->visibleQuotes($userId)->find($editingQuoteId) : null;

        if ($editingQuote !== null) {
            $this->authorize('update', $editingQuote);
            $editingQuote->load('relatedQuotes');
        }

        $linkableQuotes = $this->visibleQuotes($userId)
            ->when($editingQuote !== null, fn ($query) => $query->where('id', '<>', $editingQuote->id))
            ->latest()
            ->limit(100)
            ->get(['id', 'text', 'kind', 'user_id']);

        $publicQuotes = Quote::query()->where('visibility', 'public');
        $publicCards = (clone $publicQuotes)->count();
        $publicLinks = DB::table('quote_links as links')
            ->join('quotes as source_quotes', 'source_quotes.id', '=', 'links.quote_id')
            ->join('quotes as target_quotes', 'target_quotes.id', '=', 'links.related_quote_id')
            ->where('source_quotes.visibility', 'public')
            ->where('target_quotes.visibility', 'public');
        $insights = [
            'cards' => $publicCards,
            'links' => (clone $publicLinks)->count(),
            'members' => (clone $publicQuotes)->distinct()->count('user_id'),
            'categories' => (clone $publicQuotes)->distinct()->count('category'),
        ];

        $monthNames = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        $months = collect(range(5, 0))->map(fn (int $offset): Carbon => now()->startOfMonth()->subMonths($offset));
        $rangeStart = $months->first()->copy();
        $cardActivity = (clone $publicQuotes)->where('created_at', '>=', $rangeStart)->get(['created_at'])
            ->groupBy(fn (Quote $quote): string => $quote->created_at->format('Y-m'));
        $linkActivity = (clone $publicLinks)->where('links.created_at', '>=', $rangeStart)->get(['links.created_at'])
            ->groupBy(fn ($link): string => Carbon::parse($link->created_at)->format('Y-m'));
        $monthlyActivity = $months->map(fn (Carbon $month): array => [
            'label' => $monthNames[(int) $month->format('n') - 1],
            'cards' => $cardActivity->get($month->format('Y-m'), collect())->count(),
            'links' => $linkActivity->get($month->format('Y-m'), collect())->count(),
        ]);

        $kindCounts = (clone $publicQuotes)->select('kind', DB::raw('count(*) as aggregate'))
            ->groupBy('kind')->pluck('aggregate', 'kind');
        $kindLabels = ['quote' => 'اقتباسات', 'idea' => 'أفكار', 'note' => 'ملاحظات'];
        $kinds = collect($kindLabels)->map(fn (string $label, string $value): array => [
            'key' => $value,
            'label' => $label,
            'count' => (int) $kindCounts->get($value, 0),
        ]);
        $kindTotal = max($kinds->sum('count'), 1);
        $runningPercent = 0;
        $kinds = $kinds->map(function (array $kind) use ($kindTotal, &$runningPercent): array {
            $kind['percent'] = (int) round(($kind['count'] / $kindTotal) * 100);
            $kind['start'] = $runningPercent;
            $runningPercent += $kind['percent'];

            return $kind;
        });
        $chartColors = ['quote' => '#1d4ed8', 'idea' => '#4b82ee', 'note' => '#67a5ec'];
        $donutStops = $kinds->map(fn (array $kind): string => $chartColors[$kind['key']].' '.$kind['start'].'% '.($kind['start'] + $kind['percent']).'%')->implode(', ');
        $donutStyle = $publicCards > 0 ? 'conic-gradient('.$donutStops.')' : 'conic-gradient(#e2e8f0 0% 100%)';
        $maximumActivity = max(1, $monthlyActivity->max(fn (array $month): int => max($month['cards'], $month['links'])));
        $chartCoordinates = $monthlyActivity->values()->map(function (array $month, int $index) use ($maximumActivity): array {
            $x = 52 + ($index * (558 / 5));
            $cardY = 188 - (($month['cards'] / $maximumActivity) * 145);
            $linkY = 188 - (($month['links'] / $maximumActivity) * 145);

            return ['x' => round($x, 1), 'cardY' => round($cardY, 1), 'linkY' => round($linkY, 1)];
        });
        $cardPoints = $chartCoordinates->map(fn (array $point): string => $point['x'].','.$point['cardY'])->implode(' ');
        $linkPoints = $chartCoordinates->map(fn (array $point): string => $point['x'].','.$point['linkY'])->implode(' ');
        $cardAreaPoints = '52,188 '.$cardPoints.' 610,188';

        return view('welcome', [
            'page' => 'Home',
            'quotes' => $quotes,
            'search' => $search,
            'kind' => $kind,
            'category' => $category,
            'editingQuote' => $editingQuote,
            'selectedRelatedQuoteIds' => $editingQuote?->relatedQuotes->pluck('id')->all() ?? [],
            'linkableQuotes' => $linkableQuotes,
            'categories' => self::CATEGORIES,
            'isMyLibrary' => $request->boolean('mine') && $userId !== null,
            'insights' => $insights,
            'monthlyActivity' => $monthlyActivity,
            'chartCoordinates' => $chartCoordinates,
            'cardPoints' => $cardPoints,
            'linkPoints' => $linkPoints,
            'cardAreaPoints' => $cardAreaPoints,
            'kinds' => $kinds,
            'donutStyle' => $donutStyle,
        ]);
    }

    public function network(Request $request): View
    {
        $userId = $request->user()?->id;
        $items = $this->visibleQuotes($userId)
            ->with([
                'user',
                'relatedQuotes' => function ($relation) use ($userId): void {
                    $this->applyVisibility($relation, $userId);
                    $relation->with('user');
                },
            ])
            ->latest()
            ->get()
            ->sortBy('category')
            ->values();

        $nodePositions = [];
        $categoryGroups = $items->groupBy('category')->sortKeys()->values();
        $categoryCount = max($categoryGroups->count(), 1);

        foreach ($categoryGroups as $groupIndex => $group) {
            $baseAngle = -M_PI_2 + (2 * M_PI * $groupIndex / $categoryCount);
            $groupSize = $group->count();
            $spread = min(0.34, max(0.08, ($groupSize - 1) * 0.09));

            foreach ($group->values() as $itemIndex => $item) {
                $offset = $groupSize > 1
                    ? (($itemIndex / ($groupSize - 1)) - 0.5) * $spread
                    : 0;
                $angle = $baseAngle + $offset;
                $radius = 265 + (($itemIndex % 2) * 22);
                $x = 500 + (cos($angle) * $radius);
                $y = 325 + (sin($angle) * $radius * 0.68);

                $nodePositions[$item->id] = [
                    'x' => round($x, 1),
                    'y' => round($y, 1),
                    'labelX' => round($x + ($x < 500 ? -14 : 14), 1),
                    'labelY' => round($y + 4, 1),
                    'anchor' => $x < 500 ? 'end' : 'start',
                ];
            }
        }

        $edges = $items->flatMap(function (Quote $item): Collection {
            return $item->relatedQuotes->map(fn (Quote $relatedQuote): array => [
                'from' => $item,
                'to' => $relatedQuote,
                'type' => $relatedQuote->pivot->relation_type,
            ]);
        });

        return view('network', [
            'page' => 'Network',
            'items' => $items,
            'nodePositions' => $nodePositions,
            'edges' => $edges,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateKnowledgeCard($request);
        $relatedQuoteIds = $this->validatedRelatedQuoteIds($request, $validated['related_quote_ids'] ?? []);
        $quote = $request->user()->quotes()->create($this->knowledgeCardData($validated));

        $this->syncRelatedQuotes($quote, $relatedQuoteIds, $validated['relation_type'] ?? 'related');

        return redirect()->route('home')->with('status', 'تم حفظ بطاقتك المعرفية.');
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('update', $quote);

        $validated = $this->validateKnowledgeCard($request);
        $relatedQuoteIds = $this->validatedRelatedQuoteIds($request, $validated['related_quote_ids'] ?? [], $quote->id);
        $quote->update($this->knowledgeCardData($validated));
        $this->syncRelatedQuotes($quote, $relatedQuoteIds, $validated['relation_type'] ?? 'related');

        return redirect()->route('home')->with('status', 'تم تحديث البطاقة المعرفية.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $this->authorize('delete', $quote);
        $quote->delete();

        return redirect()->route('home')->with('status', 'تم حذف البطاقة المعرفية.');
    }

    public function toggleLike(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($quote->visibility === 'public' || $quote->user_id === $request->user()->id, 404);
        $quote->likes()->toggle($request->user()->id);

        return back();
    }

    public function toggleRepost(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($quote->visibility === 'public' || $quote->user_id === $request->user()->id, 404);
        $quote->reposts()->toggle($request->user()->id);

        return back();
    }

    private function visibleQuotes(?int $userId): Builder
    {
        return Quote::query()->where(function ($query) use ($userId): void {
            $query->where('visibility', 'public');

            if ($userId !== null) {
                $query->orWhere('user_id', $userId);
            }
        });
    }

    private function applyVisibility($query, ?int $userId): void
    {
        $query->where(function ($visibleQuery) use ($userId): void {
            $visibleQuery->where('visibility', 'public');

            if ($userId !== null) {
                $visibleQuery->orWhere('user_id', $userId);
            }
        });
    }

    /** @return array<string, mixed> */
    private function validateKnowledgeCard(Request $request): array
    {
        $textMaximum = $request->input('kind') === 'quote' ? 155 : 2000;

        return $request->validate([
            'kind' => ['required', 'in:quote,idea,note'],
            'text' => ['required', 'string', 'min:3', 'max:'.$textMaximum],
            'source' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'in:'.implode(',', array_keys(self::CATEGORIES))],
            'tags_input' => ['nullable', 'string', 'max:300'],
            'visibility' => ['required', 'in:public,private'],
            'relation_type' => ['nullable', 'in:related,supports,challenges,extends'],
            'related_quote_ids' => ['nullable', 'array', 'max:5'],
            'related_quote_ids.*' => ['integer', 'distinct', 'exists:quotes,id'],
        ]);
    }

    /** @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function knowledgeCardData(array $validated): array
    {
        $tags = preg_split('/[,،]/u', $validated['tags_input'] ?? '') ?: [];
        $validated['tags'] = collect($tags)
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->take(8)
            ->values()
            ->all();

        unset($validated['tags_input'], $validated['relation_type'], $validated['related_quote_ids']);

        return $validated;
    }

    /** @param array<int, int|string> $relatedQuoteIds
     * @return array<int, int>
     */
    private function validatedRelatedQuoteIds(Request $request, array $relatedQuoteIds, ?int $currentQuoteId = null): array
    {
        $relatedQuoteIds = collect($relatedQuoteIds)
            ->map(fn (int|string $id): int => (int) $id)
            ->unique()
            ->reject(fn (int $id): bool => $id === $currentQuoteId)
            ->values();

        if ($relatedQuoteIds->isEmpty()) {
            return [];
        }

        $visibleIds = $this->visibleQuotes($request->user()->id)
            ->whereIn('id', $relatedQuoteIds)
            ->pluck('id');

        if ($visibleIds->count() !== $relatedQuoteIds->count()) {
            throw ValidationException::withMessages([
                'related_quote_ids' => 'You can only link to public cards or your own private cards.',
            ]);
        }

        return $visibleIds->all();
    }

    /** @param array<int, int> $relatedQuoteIds */
    private function syncRelatedQuotes(Quote $quote, array $relatedQuoteIds, string $relationType): void
    {
        $links = collect($relatedQuoteIds)->mapWithKeys(fn (int $id): array => [
            $id => ['relation_type' => $relationType],
        ])->all();

        $quote->relatedQuotes()->sync($links);
    }
}
