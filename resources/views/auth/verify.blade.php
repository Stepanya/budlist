@extends('auth.layout')

@section('title', 'Verify · Budlist')
@section('heading', 'Enter your code')
@section('subtitle', 'We sent a 6-digit code to <strong>' . e($email) . '</strong>.')

@section('form')
    <form method="POST" action="{{ route('verify') }}" class="auth-form" autocomplete="off">
        @csrf

        <label class="field-label" for="otp">Verification code</label>
        <input class="field-input otp-input" type="text" name="otp" id="otp" inputmode="numeric"
               pattern="[0-9]*" maxlength="6" required autofocus autocomplete="one-time-code"
               placeholder="••••••">

        <button type="submit" class="btn-solid auth-submit">Verify &amp; continue</button>
    </form>

    <form method="POST" action="{{ route('verify.resend') }}" class="auth-resend">
        @csrf
        <button type="submit" class="auth-link-btn">Didn&rsquo;t get it? Resend code</button>
    </form>
@endsection

@section('footer')
    <a href="{{ route('login') }}">Use a different account</a>
@endsection
