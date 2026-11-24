@extends('layout.auth')
@section('title', 'Reset password')
@section('content')
<h2 class="text-2xl font-semibold">Choose a new password</h2>
<p class="mt-2 text-sm leading-6 text-[#858496]">Use at least 8 characters to secure your account.</p>
<form action="{{ route('password.update') }}" method="POST" class="mt-8 space-y-5">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    @error('token')<p role="alert" class="text-xs text-red-600">{{ $message }}</p>@enderror
    <x-input name="email" label="Email address" type="email" :value="$email" required autocomplete="username" maxlength="255" />
    <x-input name="password" label="New password" type="password" required autocomplete="new-password" minlength="8" />
    <x-input name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" minlength="8" />
    <button class="inline-flex w-full cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Reset password</button>
</form>
<p class="mt-7 text-center text-xs text-[#858496]"><a href="{{ route('password.request') }}" class="font-medium text-[#270CC5] hover:underline">Request a new reset link</a></p>
@endsection
