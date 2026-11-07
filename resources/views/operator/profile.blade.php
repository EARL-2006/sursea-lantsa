@extends('layouts.panel')

@section('title', 'Business Profile - Operator Portal')
@section('crumb', 'Profile')

@section('content')
@php
    $profile = session('operator_profile', [
        'business' => 'Isla Norte Transport',
        'lantsa' => 'MV Isla Norte',
        'service' => 'Passenger & cargo',
        'seats' => '40',
        'email' => 'islanorte@sursea.example',
        'contact' => '09123123123',
        'address' => 'Surigao City, Surigao del Norte',
    ]);
@endphp

<form method="POST" action="{{ route('operator.profile.save') }}">
    @csrf

    <div class="page-head">
        <div>
            <p class="eyebrow">ACCOUNT</p>
            <h1>Business profile</h1>
            <p class="subtitle">Your registered business details and assigned vessel as approved by the administrator.</p>
        </div>
        <button type="submit" class="btn btn-blue">Save changes</button>
    </div>

    <div class="settings-grid">
        <section class="box">
            <h2>Operator &amp; vessel information</h2>

            <div class="form-two">
                <div class="form-field">
                    <label for="business">Business name</label>
                    <input type="text" id="business" name="business" value="{{ old('business', $profile['business']) }}" required>
                </div>
                <div class="form-field">
                    <label for="lantsa">Assigned lantsa</label>
                    <input type="text" id="lantsa" name="lantsa" value="{{ old('lantsa', $profile['lantsa']) }}" required>
                </div>
            </div>

            <div class="form-two">
                <div class="form-field">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $profile['email']) }}" required>
                </div>
                <div class="form-field">
                    <label for="contact">Contact number</label>
                    <input type="text" id="contact" name="contact" value="{{ old('contact', $profile['contact']) }}" required>
                </div>
            </div>

            <div class="form-field">
                <label for="address">Business address</label>
                <input type="text" id="address" name="address" value="{{ old('address', $profile['address']) }}" required>
            </div>

            <p class="table-note">Route or vessel capacity changes require administrator review.</p>
        </section>

        <section class="box">
            <h2>Permit &amp; verification status</h2>
            <div class="policy-list">
                <div class="policy">
                    <strong>Approval status</strong>
                    <span>Approved by administrator</span>
                </div>
                <div class="policy">
                    <strong>Service type</strong>
                    <span>{{ $profile['service'] }} · {{ $profile['seats'] }} passenger seats</span>
                </div>
                <div class="policy">
                    <strong>Submitted documents</strong>
                    <span>Business permit, Government ID, Vessel registration (3 of 3 verified)</span>
                </div>
            </div>
        </section>
    </div>
</form>
@endsection

