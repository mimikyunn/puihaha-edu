@extends('layout.auth')
@section('title', 'Sign up')
@section('content')
<h2 class="text-2xl font-semibold">Create an account</h2><p class="mt-2 text-sm leading-6 text-[#858496]">Sign up for an administrator account to manage PUIHAHA memberships.</p>
<form action="{{ route('register.store') }}" method="POST" class="mt-7 space-y-5">
    @csrf
    <x-input name="name" label="Full name" required autocomplete="name" maxlength="255" />
    <x-input name="email" label="Email address" type="email" required autocomplete="username" maxlength="255" />
    <x-input name="password" label="Password" type="password" required autocomplete="new-password" minlength="8" placeholder="At least 8 characters" />
    <x-input name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" minlength="8" />
    <button class="w-full inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Sign up</button>
</form>
<p class="mt-7 text-center text-xs text-[#858496]">Already have an account? <a href="{{ route('login') }}" class="font-medium text-[#270CC5] hover:underline">Sign in</a></p>
@endsection
