@extends('layouts.auth')

@section('title', 'Create Account - SURSEA Lantsa Hub')

@section('content')
@php $activeRole = old('role', 'passenger'); @endphp

<aside class="auth-side">
    <p class="auth-brand">SURSEA LANTSA HUB</p>
    <h1>Your next crossing starts here.</h1>
    <p class="auth-text">Plan your island trip, reserve your seat, and keep your tickets together.</p>

    <ul class="auth-steps">
        <li><span>01</span> Explore routes and departures</li>
        <li><span>02</span> Reserve a seat or send cargo</li>
        <li><span>03</span> Keep your ticket ready to board</li>
    </ul>

    <p class="auth-foot">Connecting Surigao City and its island communities.</p>
</aside>

<section class="auth-card">
    <h2>Create your account</h2>
    <p class="muted">Choose how you use SURSEA.</p>

    @if($errors->any())
        <div class="alert error-box">
            @foreach($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}">
        @csrf
        <input type="hidden" name="role" id="roleInput" value="{{ $activeRole }}">

        <span class="label">Register as</span>
        <div class="role-tabs">
            <button type="button" data-role-tab="passenger" class="{{ $activeRole === 'passenger' ? 'active' : '' }}">Passenger</button>
            <button type="button" data-role-tab="operator" class="{{ $activeRole === 'operator' ? 'active' : '' }}">Operator</button>
        </div>

        <div class="auth-grid">
            <div class="auth-field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Robert" required>
            </div>
            <div class="auth-field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="RobertZabala@gmail.com" required>
            </div>
        </div>

        <div class="auth-grid">
            <div class="auth-field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••••••" required>
            </div>
            <div class="auth-field">
                <label for="password_confirmation">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••••••" required>
            </div>
        </div>

        <div class="auth-field">
            <label for="contact">Contact number</label>
            <input type="text" id="contact" name="contact" value="{{ old('contact') }}" placeholder="Enter mobile number" required>
        </div>

        <div id="operatorFields" class="auth-grid {{ $activeRole === 'operator' ? '' : 'hidden' }}">
            <div class="auth-field">
                <label for="business">Business name</label>
                <input type="text" id="business" name="business" value="{{ old('business') }}" placeholder="e.g. Sea Breeze Transport">
            </div>
            <div class="auth-field">
                <label for="lantsa">Lantsa / Vessel name</label>
                <input type="text" id="lantsa" name="lantsa" value="{{ old('lantsa') }}" placeholder="e.g. MV Sea Breeze">
            </div>
        </div>

        <p class="muted small-note">By registering, you agree to the Terms of Service and Privacy Policy.</p>

        <button
            type="submit"
            id="submitBtn"
            class="btn btn-blue btn-block"
            data-passenger-text="Create passenger account"
            data-operator-text="Submit operator application"
        >{{ $activeRole === 'operator' ? 'Submit operator application' : 'Create passenger account' }}</button>
    </form>

    <p class="switch-link">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
</section>
@endsection

