    <nav class="sticky top-0 z-50 border-b border-blue-100 bg-white/95 shadow-sm backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-1.5">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold text-blue-950 transition hover:bg-blue-50" aria-label="العودة إلى الصفحة الرئيسية">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-7 9 7"/><path d="M5 9v11h14V9M9 20v-6h6v6"/></svg>
            <span>الرئيسية</span>
        </a>

        <div class="flex items-center gap-3">
            @auth
                <span class="hidden text-sm text-blue-900/80 sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-primary px-4 py-2 text-sm" type="submit">تسجيل الخروج</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-blue-950 hover:bg-blue-50">دخول</a>
                <a href="{{ route('register') }}" class="btn-primary px-4 py-2 text-sm">إنشاء حساب</a>
            @endauth
        </div>
    </div>
</nav>
