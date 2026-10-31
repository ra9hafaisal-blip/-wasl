@extends('Layout.app')

@section('title', 'إنشاء حساب | وَصْل')

@section('content')
<div class="mx-auto grid min-h-[calc(100vh-5rem)] max-w-6xl items-center gap-10 px-4 py-10 md:grid-cols-2">
    <section class="page-intro">
        <p class="text-sm font-bold text-teal-700">مكانك بين أصحاب الأفكار</p>
        <h1 class="mt-3 text-4xl font-black">ابدأ مكتبتك المعرفية.</h1>
        <p class="mt-4 leading-8 text-slate-600">احفظ ما يلهمك، رتّبه، وابنِ روابط بين المعارف.</p>
    </section>
    <form action="{{ route('register.store') }}" method="POST" class="auth-form mx-auto w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-7 shadow-xl sm:p-9">
        @csrf
        <h2 class="mb-6 text-center text-2xl font-bold">إنشاء حساب</h2>
        @if ($errors->any())<div class="mb-5 rounded-xl bg-red-50 p-3 text-sm text-red-800" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <div class="mb-4"><label for="name" class="label">الاسم</label><input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required minlength="2" maxlength="50" class="input"><p class="mt-1 text-xs text-slate-500">من حرفين إلى 50 حرفًا.</p></div>
        <div class="mb-4"><label for="email" class="label">البريد الإلكتروني</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="input"></div>
        <div class="mb-4"><label for="password" class="label">كلمة المرور</label><input id="password" name="password" type="password" autocomplete="new-password" required minlength="8" class="input"><p class="mt-1 text-xs text-slate-500">ثمانية أحرف على الأقل.</p></div>
        <div class="mb-6"><label for="password_confirmation" class="label">تأكيد كلمة المرور</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8" class="input"></div>
        <button type="submit" class="btn-primary w-full">إنشاء الحساب</button>
        <p class="mt-5 text-center text-sm text-slate-600">لديك حساب؟ <a href="{{ route('login') }}" class="font-bold text-indigo-700 hover:underline">تسجيل الدخول</a></p>
    </form>
</div>
@endsection
