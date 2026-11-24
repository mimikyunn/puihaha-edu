@extends('layout.auth')
@section('title', 'Forgot password')
@section('content')
<h2 class="text-2xl font-semibold">Forgot your password?</h2>
<p class="mt-2 text-sm leading-6 text-[#858496]">Enter your account email and we’ll send you a link to reset your password.</p>
<form action="{{ route('password.email') }}" method="POST" class="mt-8 space-y-5">
    @csrf
    <x-input name="email" label="Email address" type="email" required autocomplete="email" maxlength="255" placeholder="you@example.com" />
    <button class="inline-flex w-full cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Email reset link</button>
</form>
<p class="mt-7 text-center text-xs text-[#858496]"><a href="{{ route('login') }}" class="font-medium text-[#270CC5] hover:underline">Back to sign in</a></p>
@endsection
