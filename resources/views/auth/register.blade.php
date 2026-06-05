@extends('auth.layout')

@section('title', 'Create account · Budlist')
@section('heading', 'Create your account')
@section('subtitle', 'We&rsquo;ll email you a one-time code to confirm it&rsquo;s you.')

@section('form')
    <form method="POST" action="{{ route('register') }}" class="auth-form" autocomplete="on">
        @csrf

        <label class="field-label" for="name">Name</label>
        <input class="field-input" type="text" name="name" id="name" value="{{ old('name') }}" required autofocus autocomplete="name" maxlength="255">

        <label class="field-label" for="email">Email</label>
        <input class="field-input" type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email">

        <label class="field-label" for="password">Password</label>
        <input class="field-input" type="password" name="password" id="password" required autocomplete="new-password" minlength="8">

        <label class="field-label" for="password_confirmation">Confirm password</label>
        <input class="field-input" type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" minlength="8">

        <button type="submit" class="btn-solid auth-submit">Create account</button>
    </form>
@endsection

@section('footer')
    Already have an account? <a href="{{ route('login') }}">Sign in</a>
@endsection
