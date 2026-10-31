@extends('Layout.app')

@section('content')
@php
    $categoryColors = [
        'mathematics' => '#2866e8',
        'physics' => '#4b82ee',
        'biology' => '#168bb8',
        'psychology' => '#665bd8',
        'business' => '#0f7394',
        'programming' => '#244ba8',
        'education' => '#788eca',
        'other' => '#729ac9',
    ];
    $relationColors = ['related' => '#8ba5c8', 'supports' => '#2866e8', 'challenges' => '#d59b2b', 'extends' => '#168bb8'];
    $relationLabels = ['related' => 'ترتبط بـ', 'supports' => 'تدعم', 'challenges' => 'تتحدى', 'extends' => 'توسّع'];
    $ideaItems = $items->where('kind', 'idea');
@endphp

<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <header class="surface-card hero-panel mb-5 grid gap-6 p-7 sm:p-10 lg:grid-cols-[1fr_auto] lg:items-end">
        <div class="surface-content">
            <p class="mono-label text-[11px] font-bold text-blue-800">وَصْل · خريطة المعرفة</p>
            <h1 class="mt-4 text-3xl font-black leading-tight text-[#172033] sm:text-5xl">كل فكرة لها خيط</h1>
            <p class="mt-4 max-w-2xl leading-8 text-slate-600">من مركز وَصْل تتفرع الأفكار، وتلتقي المجالات عبر علاقات حقيقية بين البطاقات.</p>
        </div>
        <div class="surface-content flex gap-3">
            <div class="min-w-24 rounded-2xl bg-white/75 p-4"><p class="mono-label text-[10px] font-bold text-blue-800">عُقد</p><p class="mt-2 text-3xl font-black text-blue-950">{{ $items->count() }}</p></div>
            <div class="min-w-24 rounded-2xl bg-blue-600 p-4 text-white"><p class="mono-label text-[10px] font-bold text-blue-100">روابط</p><p class="mt-2 text-3xl font-black">{{ $edges->count() }}</p></div>
        </div>
    </header>

    <div class="grid gap-5">
        <section class="surface-card p-3 sm:p-5" aria-label="الرسم الشبكي التفاعلي للأفكار">
            <div class="surface-content flex flex-wrap items-center justify-between gap-3 px-2 pb-2">
                <div><p class="mono-label text-[10px] font-bold text-blue-800">01 / الرسم الشبكي</p><h2 class="mt-1 text-lg font-bold">اختيار أي عقدة يفتح البطاقة</h2></div>
                <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-900">الخطوط المتصلة تعرض الروابط المحفوظة</span>
            </div>

            <div class="surface-content graph-scroll rounded-2xl border border-blue-100 bg-white/55">
                @if ($items->isEmpty())
                    <div class="grid min-h-80 place-items-center p-8 text-center text-slate-600">أضف بطاقات واربط بينها لتبدأ الخريطة بالظهور.</div>
                @else
                <svg viewBox="0 0 1000 650" class="knowledge-graph" role="img" aria-labelledby="graph-title graph-description">
                    <title id="graph-title">شبكة الأفكار في وَصْل</title>
                    <desc id="graph-description">رسم يضع وَصْل في المركز ويربط بطاقات الاقتباسات والأفكار حسب المجال ونوع العلاقة.</desc>
                    <defs>
                        <radialGradient id="wasl-core" cx="30%" cy="24%" r="82%"><stop offset="0%" stop-color="#d5e6ff"/><stop offset="16%" stop-color="#8eb8ff"/><stop offset="52%" stop-color="#3f7cf0"/><stop offset="82%" stop-color="#245ed5"/><stop offset="100%" stop-color="#123c98"/></radialGradient>
                        <linearGradient id="core-rim" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ffffff" stop-opacity=".92"/><stop offset="48%" stop-color="#9cc2ff" stop-opacity=".7"/><stop offset="100%" stop-color="#1f56bd" stop-opacity=".88"/></linearGradient>
                        <linearGradient id="idea-thread" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#8bb6ff" stop-opacity=".15"/><stop offset="100%" stop-color="#2866e8" stop-opacity=".72"/></linearGradient>
                        <filter id="core-glow" x="-80%" y="-80%" width="260%" height="260%"><feGaussianBlur stdDeviation="15" result="blur"/><feColorMatrix in="blur" type="matrix" values="0 0 0 0 0.20 0 0 0 0 0.45 0 0 0 0 0.95 0 0 0 .42 0"/><feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                    </defs>

                    <g fill="none" stroke="#9eb9e2" stroke-width="1"><circle cx="500" cy="325" r="112" opacity=".18"/><circle cx="500" cy="325" r="205" opacity=".25" stroke-dasharray="2 8"/><circle cx="500" cy="325" r="290" opacity=".2" stroke-dasharray="2 10"/></g>

                    <g fill="none" stroke="url(#idea-thread)" stroke-width="1.5">
                        @foreach ($ideaItems as $idea)
                            @if (isset($nodePositions[$idea->id]))<line x1="500" y1="325" x2="{{ $nodePositions[$idea->id]['x'] }}" y2="{{ $nodePositions[$idea->id]['y'] }}" opacity=".82"/>@endif
                        @endforeach
                    </g>

                    <g fill="none" stroke-width="1.7" stroke-linecap="round">
                        @foreach ($edges as $edge)
                            @if (isset($nodePositions[$edge['from']->id], $nodePositions[$edge['to']->id]))
                                <line x1="{{ $nodePositions[$edge['from']->id]['x'] }}" y1="{{ $nodePositions[$edge['from']->id]['y'] }}" x2="{{ $nodePositions[$edge['to']->id]['x'] }}" y2="{{ $nodePositions[$edge['to']->id]['y'] }}" stroke="{{ $relationColors[$edge['type']] ?? '#8ba5c8' }}" opacity=".62"><title>{{ $relationLabels[$edge['type']] ?? 'ترتبط بـ' }}: {{ \Illuminate\Support\Str::limit($edge['from']->text, 70) }} ← {{ \Illuminate\Support\Str::limit($edge['to']->text, 70) }}</title></line>
                            @endif
                        @endforeach
                    </g>

                    @foreach ($items as $item)
                        @php
                            $point = $nodePositions[$item->id] ?? ['x' => 500, 'y' => 325, 'labelX' => 500, 'labelY' => 325, 'anchor' => 'middle'];
                            $nodeColor = $categoryColors[$item->category] ?? '#729ac9';
                            $nodeRadius = $item->kind === 'idea' ? 9 : ($item->kind === 'note' ? 7 : 5.5);
                        @endphp
                        <a href="{{ route('home', ['q' => $item->text]) }}" class="graph-node">
                            <title>{{ $categories[$item->category] ?? 'أخرى' }} · {{ $item->user?->name }} · {{ $item->text }}</title>
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="{{ $nodeRadius + 7 }}" fill="{{ $nodeColor }}" opacity=".13"/>
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="{{ $nodeRadius }}" fill="{{ $nodeColor }}" stroke="#fff" stroke-width="2.5"/>
                            @if ($item->kind === 'idea')
                                <text x="{{ $point['labelX'] }}" y="{{ $point['labelY'] }}" text-anchor="{{ $point['anchor'] }}" direction="rtl" class="graph-label">
                                    <tspan x="{{ $point['labelX'] }}" fill="{{ $nodeColor }}" font-size="11" font-weight="700">{{ $categories[$item->category] ?? 'أخرى' }}</tspan>
                                    <tspan x="{{ $point['labelX'] }}" dy="16" fill="#334155" font-size="11">{{ \Illuminate\Support\Str::limit($item->text, 20) }}</tspan>
                                </text>
                            @endif
                        </a>
                    @endforeach

                    <g filter="url(#core-glow)">
                        <circle cx="500" cy="325" r="136" fill="#4b82ee" opacity=".08"/>
                        <circle cx="500" cy="325" r="126" fill="url(#wasl-core)" stroke="url(#core-rim)" stroke-width="2.5"/>
                        <ellipse cx="470" cy="291" rx="48" ry="22" fill="#fff" opacity=".13" transform="rotate(-32 470 291)"/>
                        <circle cx="500" cy="325" r="134" fill="none" stroke="#6da0ff" stroke-width="1.5" opacity=".62"/>
                    </g>
                    <text x="500" y="318" fill="white" text-anchor="middle" font-size="36" font-weight="800" letter-spacing=".5">وَصْل</text>
                    <text x="500" y="350" fill="#eaf2ff" text-anchor="middle" font-size="14" font-weight="600" letter-spacing=".3">مركز المعرفة</text>
                </svg>
                @endif
            </div>
        </section>

        <aside class="grid content-start gap-4 md:grid-cols-2 xl:grid-cols-3">
            <section class="surface-card p-5">
                <div class="surface-content"><p class="mono-label text-[10px] font-bold text-blue-800">02 / المجالات</p><h2 class="mt-1 text-lg font-bold">دليل الألوان</h2>
                    <ul class="mt-4 grid gap-3 text-sm">
                        @foreach ($categories as $categoryKey => $categoryLabel)
                            @if ($items->contains('category', $categoryKey))<li class="flex items-center gap-2"><i class="h-3 w-3 rounded-full" style="background-color: {{ $categoryColors[$categoryKey] ?? '#729ac9' }}"></i><span>{{ $categoryLabel }}</span><span class="ms-auto text-xs text-slate-500">{{ $items->where('category', $categoryKey)->count() }}</span></li>@endif
                        @endforeach
                    </ul>
                </div>
            </section>

            <section class="surface-card p-5">
                <div class="surface-content"><p class="mono-label text-[10px] font-bold text-blue-800">03 / طبيعة الوصلات</p><h2 class="mt-1 text-lg font-bold">ماذا تعني الخيوط؟</h2>
                    <ul class="mt-4 grid gap-3 text-sm">
                        @foreach ($relationLabels as $relationKey => $relationLabel)
                            <li class="flex items-center gap-2"><i class="h-0.5 w-6 rounded-full" style="background-color: {{ $relationColors[$relationKey] }}"></i><span>{{ $relationLabel }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </section>

            <section class="surface-card p-5">
                <div class="surface-content"><p class="mono-label text-[10px] font-bold text-blue-800">04 / أفكار بارزة</p><h2 class="mt-1 text-lg font-bold">عُقد نشطة في الشبكة</h2>
                    <div class="mt-3 grid gap-2">
                        @forelse ($ideaItems as $idea)
                            <a href="{{ route('home', ['q' => $idea->text]) }}" class="rounded-xl border border-blue-100 bg-white/65 p-3 transition hover:border-blue-300 hover:bg-white">
                                <span class="block text-[11px] font-bold text-blue-700">{{ $categories[$idea->category] ?? 'أخرى' }}</span>
                                <span class="mt-1 block text-sm font-semibold leading-6 text-slate-800">{{ \Illuminate\Support\Str::limit($idea->text, 74) }}</span>
                            </a>
                        @empty
                            <p class="mt-3 text-sm text-slate-600">إضافة بطاقات من نوع «فكرة» تعرضها هنا.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <details class="surface-card mt-5 p-5 sm:p-6">
        <summary class="surface-content flex cursor-pointer list-none items-center justify-between gap-3 font-bold text-blue-950"><span>تفاصيل الروابط المسجلة</span><span class="rounded-full bg-blue-100 px-3 py-1 text-xs text-blue-900">{{ $edges->count() }} رابط</span></summary>
        <div class="surface-content mt-4 grid gap-2 border-t border-blue-100 pt-4">
            @forelse ($edges as $edge)
                <div class="grid items-center gap-2 rounded-xl bg-white/70 p-3 text-sm sm:grid-cols-[1fr_auto_1fr]">
                    <a class="font-medium text-slate-700 hover:text-blue-700" href="{{ route('home', ['q' => $edge['from']->text]) }}">{{ \Illuminate\Support\Str::limit($edge['from']->text, 70) }}</a>
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-center text-xs font-bold text-blue-900">{{ $relationLabels[$edge['type']] ?? 'ترتبط بـ' }}</span>
                    <a class="font-medium text-slate-700 hover:text-blue-700" href="{{ route('home', ['q' => $edge['to']->text]) }}">{{ \Illuminate\Support\Str::limit($edge['to']->text, 70) }}</a>
                </div>
            @empty
                <p class="text-sm text-slate-600">لا توجد روابط بعد. اربط البطاقات من نموذج إضافة الفكرة.</p>
            @endforelse
        </div>
    </details>
</section>
@endsection
