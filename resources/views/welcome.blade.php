@extends('Layout.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <header class="wasl-hero mb-6">
        <div class="wasl-hero-content">
            <div class="surface-content wasl-hero-brand">
                <span class="wasl-wordmark" aria-label="وَصْل">وَصْل</span>
                <span class="wasl-brand-connector" aria-hidden="true"></span>
                <p class="wasl-brand-tagline">مكتبة معرفية تجمع الاقتباسات والأفكار لتكشف روابطها</p>
            </div>

            <div class="surface-content wasl-hero-copy">
                <h1 class="wasl-hero-title font-black tracking-tight text-[#172033]">كل اقتباس وفكرة <span class="text-blue-700">لها وَصْل</span></h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">اجمع الاقتباسات والأفكار والملاحظات في مكتبة واحدة، واكتشف الروابط التي تجمع بينها.</p>

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <form action="{{ route('home') }}" method="GET" class="wasl-hero-search">
                        <label for="search" class="sr-only">البحث في المكتبة</label>
                        <input id="search" name="q" value="{{ $search }}" placeholder="ابحث عن فكرة أو اقتباس أو مجال" class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0">
                        <button class="shrink-0 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-300">بحث</button>
                    </form>
                    <a href="{{ route('network') }}" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white/75 px-5 py-3 text-sm font-bold text-blue-950 transition hover:border-blue-400 hover:bg-white">استكشف شبكة الأفكار <span aria-hidden="true">↗</span></a>
                    @auth<a href="{{ route('home', ['mine' => 1]) }}" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white/75 px-5 py-3 text-sm font-bold text-blue-950 transition hover:border-blue-400 hover:bg-white">مكتبتي</a>@endauth
                </div>
            </div>

            <div class="surface-content flex flex-wrap items-center justify-between gap-4 border-t border-blue-200/60 pt-5">
                <p class="text-sm font-semibold text-slate-500">من فكرة صغيرة تبدأ شبكة من المعرفة</p>
                <div class="flex flex-wrap gap-2 text-xs font-semibold text-blue-950/75">
                    <span class="rounded-full bg-white/75 px-3 py-2">اقتباسات</span>
                    <span class="rounded-full bg-white/75 px-3 py-2">أفكار</span>
                    <span class="rounded-full bg-white/75 px-3 py-2">ملاحظات</span>
                    <span class="rounded-full bg-white/75 px-3 py-2">مجالات مترابطة</span>
                </div>
            </div>
        </div>
    </header>

    <div class="my-5 flex flex-wrap items-center gap-2 rounded-2xl border border-white/50 bg-white/20 p-3 backdrop-blur-sm">
        <span class="mono-label me-1 text-[10px] font-bold text-blue-950/70">تصفية</span>
        <a href="{{ route('home') }}" class="filter-chip {{ !$kind ? 'filter-chip-active' : '' }}">الكل</a>
        @foreach (['quote' => 'اقتباسات', 'idea' => 'أفكار', 'note' => 'ملاحظات'] as $kindValue => $kindLabel)
            <a href="{{ route('home', array_filter(['kind' => $kindValue, 'category' => $category, 'q' => $search])) }}" class="filter-chip {{ $kind === $kindValue ? 'filter-chip-active' : '' }}">{{ $kindLabel }}</a>
        @endforeach
        <form method="GET" action="{{ route('home') }}" class="flex gap-2">
            @if ($search)<input type="hidden" name="q" value="{{ $search }}">@endif
            @if ($kind)<input type="hidden" name="kind" value="{{ $kind }}">@endif
            <select name="category" class="rounded-full border border-white/70 bg-[#f3f1eb] px-4 py-2 text-sm text-slate-800" onchange="this.form.submit()">
                <option value="">كل المجالات</option>
                @foreach ($categories as $categoryValue => $categoryLabel)<option value="{{ $categoryValue }}" @selected($category === $categoryValue)>{{ $categoryLabel }}</option>@endforeach
            </select>
        </form>
        @auth<a href="{{ route('home', ['mine' => 1]) }}" class="filter-chip {{ $isMyLibrary ? 'filter-chip-active' : '' }}">مكتبتي</a>@endauth
    </div>

    @if (session('status'))<div class="mb-5 rounded-2xl border border-white/70 bg-[#f3f1eb] p-4 text-teal-900 shadow-sm">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800"><p class="font-bold">تحقق من البيانات التالية:</p>@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    @auth
    <details class="surface-card mb-5 p-5" {{ $errors->any() && !$editingQuote ? 'open' : '' }}>
        <summary class="surface-content flex cursor-pointer list-none items-center justify-between gap-4 text-lg font-bold text-blue-950"><span>إضافة بطاقة إلى المكتبة</span><span class="grid h-9 w-9 place-items-center rounded-full bg-blue-600 text-xl text-white">＋</span></summary>
        <form action="{{ route('quotes.store') }}" method="POST" class="surface-content mt-5 grid gap-4 border-t border-blue-100 pt-5">@csrf
            @include('quotes.form-fields', ['formQuote' => null, 'formId' => 'new', 'selectedRelatedQuoteIds' => []])
            <button class="btn-primary justify-self-start">حفظ في مكتبتي</button>
        </form>
    </details>
    @else
    <div class="surface-card mb-5 flex flex-wrap items-center justify-between gap-5 p-6 sm:p-8"><div class="surface-content"><p class="text-xl font-bold text-blue-950">هل توجد فكرة تستحق الحفظ؟</p><p class="mt-1 text-slate-600">إنشاء حساب يتيح بناء مكتبة معرفية خاصة.</p></div><a href="{{ route('register') }}" class="surface-content inline-flex rounded-xl bg-blue-600 px-5 py-3 font-bold text-white transition hover:bg-blue-700">إنشاء حساب في وَصْل</a></div>
    @endauth

    @if ($editingQuote)
        <section class="surface-card mb-5 p-6"><div class="surface-content mb-4 flex justify-between"><h2 class="text-xl font-bold">تعديل البطاقة</h2><a href="{{ route('home') }}" class="text-slate-500">إلغاء</a></div>
            <form action="{{ route('quotes.update', $editingQuote) }}" method="POST" class="surface-content grid gap-4">@csrf @method('PUT')
                @include('quotes.form-fields', ['formQuote' => $editingQuote, 'formId' => 'edit-'.$editingQuote->id])
                <button class="btn-primary justify-self-start">حفظ التعديلات</button>
            </form>
        </section>
    @endif

    <div class="mb-4 flex items-end justify-between gap-4 px-1">
        <div><p class="mono-label text-[11px] font-bold text-blue-950/70">03 / مكتبة المجتمع</p><h2 class="mt-2 text-2xl font-black text-blue-950 sm:text-3xl">أفكار تستحق الاكتشاف</h2></div>
        <span class="rounded-full bg-white/80 px-3 py-1.5 text-sm font-semibold text-blue-950">{{ $quotes->total() }} بطاقة</span>
    </div>

    @if ($quotes->isEmpty())
        <div class="surface-card p-10 text-center"><div class="surface-content"><p class="text-lg font-bold">لا توجد بطاقات تطابق البحث</p><p class="mt-2 text-slate-600">يمكن اختيار مجال آخر أو إضافة أول فكرة.</p></div></div>
    @else
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($quotes as $quote)
        <article class="surface-card quote-card flex flex-col p-5 sm:p-6" data-reveal style="--reveal-delay: {{ ($loop->index % 3) * 85 }}ms">
            <div class="surface-content flex flex-wrap items-center gap-2"><span class="badge">{{ ['quote' => 'اقتباس', 'idea' => 'فكرة', 'note' => 'ملاحظة'][$quote->kind] ?? 'فكرة' }}</span><span class="text-xs font-semibold text-blue-800">{{ $categories[$quote->category] ?? 'أخرى' }}</span>@if ($quote->visibility === 'private')<span class="text-xs text-amber-700">خاصة</span>@endif</div>
            <blockquote class="surface-content my-5 text-lg font-semibold leading-8 text-[#172033]">{{ $quote->text }}</blockquote>
            @if ($quote->source)<p class="surface-content text-sm text-slate-500">المصدر: {{ $quote->source }}</p>@endif
            @if ($quote->note)<p class="surface-content mt-3 rounded-xl bg-white/70 p-3 text-sm leading-6 text-slate-600">{{ $quote->note }}</p>@endif
            @if ($quote->tags)<div class="surface-content mt-3 flex flex-wrap gap-1">@foreach ($quote->tags as $tag)<span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs text-blue-900">#{{ $tag }}</span>@endforeach</div>@endif
            @if ($quote->relatedQuotes->isNotEmpty())<div class="surface-content mt-4 border-t border-blue-100 pt-3"><p class="mb-2 text-xs font-bold text-blue-800">أفكار مرتبطة</p>@foreach ($quote->relatedQuotes as $related)<a href="{{ route('home', ['q' => $related->text]) }}" class="mb-1 block truncate text-sm text-slate-600 hover:text-blue-700">↔ {{ $related->text }}</a>@endforeach</div>@endif
            <div class="surface-content mt-auto flex flex-wrap items-center gap-2 border-t border-blue-100 pt-4">
                @auth
                    <form action="{{ route('quotes.like', $quote) }}" method="POST">@csrf<button class="rounded-full border px-3 py-1.5 text-sm {{ $quote->liked_by_me ?? false ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white/70 text-slate-600 hover:bg-white' }}">{{ $quote->liked_by_me ?? false ? '♥ أعجبني' : '♡ إعجاب' }} · {{ $quote->likes_count }}</button></form>
                    <form action="{{ route('quotes.repost', $quote) }}" method="POST">@csrf<button class="rounded-full border px-3 py-1.5 text-sm {{ $quote->reposted_by_me ?? false ? 'border-teal-600 bg-teal-50 text-teal-700' : 'border-slate-200 bg-white/70 text-slate-600 hover:bg-white' }}">{{ $quote->reposted_by_me ?? false ? '✓ أُعيد نشره' : '⟳ إعادة نشر' }} · {{ $quote->reposts_count }}</button></form>
                @else
                    <a href="{{ route('login') }}" class="rounded-full border border-slate-200 bg-white/70 px-3 py-1.5 text-sm text-slate-600">♡ إعجاب · {{ $quote->likes_count }}</a>
                    <a href="{{ route('login') }}" class="rounded-full border border-slate-200 bg-white/70 px-3 py-1.5 text-sm text-slate-600">⟳ إعادة نشر · {{ $quote->reposts_count }}</a>
                @endauth
                <span class="me-auto text-end text-xs leading-5 text-slate-500"><span class="block text-sm">{{ $quote->user?->name ?? 'عضو وَصْل' }}</span><time datetime="{{ $quote->created_at->toIso8601String() }}">نُشر في {{ $quote->created_at->format('Y-m-d') }}</time></span>
                @can('update', $quote)<div class="flex gap-3"><a href="{{ route('home', ['edit' => $quote->id]) }}" class="text-sm font-semibold text-blue-700">تعديل</a><form action="{{ route('quotes.destroy', $quote) }}" method="POST" onsubmit="return confirm('حذف هذه البطاقة؟')">@csrf @method('DELETE')<button class="text-sm text-red-600">حذف</button></form></div>@endcan
            </div>
        </article>
        @endforeach
    </div>
    <div class="quote-pagination mt-8 rounded-2xl p-3">{{ $quotes->links() }}</div>
    @endif

    <section class="knowledge-dashboard mt-14" aria-labelledby="insights-title">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3 px-1">
            <div><p class="mono-label text-[11px] font-bold text-blue-950/70">04 / لوحة المكتبة</p><h2 id="insights-title" class="mt-2 text-2xl font-black text-blue-950 sm:text-3xl">نبض المعرفة</h2></div>
            <span class="rounded-full border border-white/60 bg-white/75 px-3 py-1.5 text-xs font-bold text-blue-900">بيانات البطاقات العامة المسجلة</span>
        </div>

        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="surface-card p-5" data-reveal style="--reveal-delay: 0ms"><div class="surface-content"><p class="text-sm font-semibold text-slate-600">بطاقات عامة</p><p class="mt-3 text-4xl font-black text-blue-950">{{ number_format($insights['cards']) }}</p><p class="mt-2 text-xs text-blue-700">اقتباسات وأفكار وملاحظات</p></div></article>
            <article class="surface-card p-5" data-reveal style="--reveal-delay: 85ms"><div class="surface-content"><p class="text-sm font-semibold text-slate-600">روابط محفوظة</p><p class="mt-3 text-4xl font-black text-blue-950">{{ number_format($insights['links']) }}</p><p class="mt-2 text-xs text-teal-700">بين البطاقات العامة</p></div></article>
            <article class="surface-card p-5" data-reveal style="--reveal-delay: 170ms"><div class="surface-content"><p class="text-sm font-semibold text-slate-600">أعضاء مشاركون</p><p class="mt-3 text-4xl font-black text-blue-950">{{ number_format($insights['members']) }}</p><p class="mt-2 text-xs text-blue-700">أصحاب بطاقات عامة</p></div></article>
            <article class="surface-card p-5" data-reveal style="--reveal-delay: 255ms"><div class="surface-content"><p class="text-sm font-semibold text-slate-600">مجالات مستخدمة</p><p class="mt-3 text-4xl font-black text-blue-950">{{ number_format($insights['categories']) }}</p><p class="mt-2 text-xs text-slate-500">من مجالات المكتبة</p></div></article>
        </div>

        <div class="grid gap-4 lg:grid-cols-[1.35fr_.65fr]">
            <article class="surface-card p-5 sm:p-7">
                <div class="surface-content flex flex-wrap items-start justify-between gap-3">
                    <div><p class="mono-label text-[10px] font-bold text-blue-800">آخر ستة أشهر</p><h3 class="mt-2 text-xl font-bold">نشاط المكتبة عبر الوقت</h3><p class="mt-1 text-sm text-slate-500">عدد البطاقات والروابط العامة المضافة كل شهر</p></div>
                </div>
                <div class="surface-content mt-5 overflow-hidden">
                    <svg viewBox="0 0 640 235" class="h-auto w-full" role="img" aria-label="رسم خطي لعدد البطاقات والروابط العامة المضافة خلال آخر ستة أشهر">
                        <defs><linearGradient id="activity-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#3978ed" stop-opacity=".22"/><stop offset="100%" stop-color="#3978ed" stop-opacity="0"/></linearGradient></defs>
                        <g stroke="#dbe3ef" stroke-width="1"><path d="M48 28H615M48 68H615M48 108H615M48 148H615M48 188H615"/></g>
                        <polygon points="{{ $cardAreaPoints }}" fill="url(#activity-fill)"/>
                        <polyline points="{{ $cardPoints }}" fill="none" stroke="#2866e8" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="{{ $linkPoints }}" fill="none" stroke="#3279b7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="7 7"/>
                        <g fill="#2866e8" stroke="#fff" stroke-width="3">@foreach ($chartCoordinates as $point)<circle cx="{{ $point['x'] }}" cy="{{ $point['cardY'] }}" r="5"/>@endforeach</g>
                        <g fill="#64748b" font-size="12" text-anchor="middle">@foreach ($chartCoordinates as $index => $point)<text x="{{ $point['x'] }}" y="218">{{ $monthlyActivity[$index]['label'] }}</text>@endforeach</g>
                    </svg>
                </div>
                <div class="surface-content mt-2 flex flex-wrap gap-5 text-xs font-semibold text-slate-600"><span class="inline-flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-blue-600"></i>البطاقات: {{ number_format($insights['cards']) }}</span><span class="inline-flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-sky-700"></i>الروابط: {{ number_format($insights['links']) }}</span></div>
            </article>

            <article class="surface-card p-5 sm:p-7">
                <div class="surface-content"><p class="mono-label text-[10px] font-bold text-blue-800">توزيع المكتبة</p><h3 class="mt-2 text-xl font-bold">أنواع البطاقات</h3><p class="mt-1 text-sm text-slate-500">النسب الفعلية من البطاقات العامة</p></div>
                <div class="surface-content my-6 flex justify-center"><div class="donut-chart" style="background: {{ $donutStyle }}" role="img" aria-label="مخطط يوضح التوزيع الفعلي لأنواع البطاقات العامة"><div class="donut-hole"><strong>{{ number_format($insights['cards']) }}</strong><span>بطاقة عامة</span></div></div></div>
                <ul class="surface-content grid grid-cols-2 gap-x-3 gap-y-3 text-sm">
                    @foreach ($kinds as $kind)
                    <li class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full" style="background-color: {{ ['quote' => '#1d4ed8', 'idea' => '#4b82ee', 'note' => '#67a5ec'][$kind['key']] }}"></i><span>{{ $kind['label'] }}</span><b class="ms-auto">{{ $kind['percent'] }}%</b></li>
                    @endforeach
                </ul>
            </article>
        </div>
    </section>
</section>
@endsection
