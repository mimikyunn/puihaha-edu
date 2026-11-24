@extends('layout.auth')
@section('title', 'Login')
@section('content')
<h2 class="text-2xl font-semibold">Welcome back!</h2><p class="mt-2 text-sm text-[#858496]">Sign in to your administrator workspace.</p>
<form action="{{ route('login.store') }}" method="POST" class="mt-8 space-y-5">
    @csrf
    <x-input name="email" label="Email address" type="email" required autocomplete="username" placeholder="you@example.com" />
    <x-input name="password" label="Password" type="password" required autocomplete="current-password" />
    <div class="flex flex-wrap items-center justify-between gap-3">
        <label class="flex cursor-pointer items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-3.5 accent-[#270CC5]">Remember me</label>
        <a href="{{ route('password.request') }}" class="text-xs font-medium text-[#270CC5] hover:underline">Forgot password?</a>
    </div>
    <button class="w-full inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Sign in</button>
</form>
<p class="mt-8 text-center text-xs text-[#858496]">Don’t have an account? <a href="{{ route('register') }}" class="font-medium text-[#270CC5] hover:underline">Sign up!</a></p>
@endsection
