@extends('auth.layout')

@section('title', 'Sign in · Budlist')
@section('heading', 'Welcome back')
@section('subtitle', 'Sign in to your budget.')

@section('form')
    <form method="POST" action="{{ route('login') }}" class="auth-form" autocomplete="on">
        @csrf

        <label class="field-label" for="email">Email</label>
        <input class="field-input" type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email">

        <label class="field-label" for="password">Password</label>
        <input class="field-input" type="password" name="password" id="password" required autocomplete="current-password">

        <label class="auth-remember">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            <span>Keep me signed in</span>
        </label>

        <button type="submit" class="btn-solid auth-submit">Sign in</button>
    </form>
@endsection

@section('footer')
    New here? <a href="{{ route('register') }}">Create an account</a>
@endsection
