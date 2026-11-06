@extends('layouts.passenger')

@section('title', 'SURSEA Lantsa Hub')

@section('content')
@php $loggedIn = session('user') && session('user')['role'] === 'passenger'; @endphp

{{-- HERO --}}
<section class="container hero">
    <div class="hero-text">
        <h1>Skip the long port lines. Secure your lantsa seat online.</h1>
        <p>Your digital gateway to island-to-island travel in Surigao City. Check daily schedules, reserve seats on local motorized outriggers, track cargo capacity, and travel with peace of mind.</p>
        <a href="{{ route('schedules') }}" class="btn btn-dark">View Live Trips @include('partials.icon', ['name' => 'arrow-right'])</a>
    </div>

    <form class="booking-card" method="GET" action="{{ route('schedules') }}">
        <div class="booking-head">
            <h2>Reserve Your Trip</h2>
            <span class="live-pill"><i></i> LIVE BOOKINGS OPEN</span>
        </div>

        <div class="booking-grid">
            <label class="booking-field">
                <span>FROM</span>
                <select name="from">
                    @foreach($places as $place)
                        <option value="{{ $place }}" @selected($place === 'Dinagat')>{{ $place }}</option>
                    @endforeach
                </select>
            </label>
            <label class="booking-field">
                <span>TO</span>
                <select name="to">
                    @foreach($places as $place)
                        <option value="{{ $place }}" @selected($place === 'Surigao City')>{{ $place }}</option>
                    @endforeach
                </select>
            </label>
            <label class="booking-field">
                <span>DATE OF TRAVEL</span>
                <input type="date" name="date" value="2026-09-26">
            </label>
            <label class="booking-field">
                <span>PASSENGERS</span>
                <input type="number" name="passengers" min="1" max="40" value="1">
            </label>
        </div>

        <div class="cargo-toggle">
            @include('partials.icon', ['name' => 'box'])
            <span>Bringing Cargo? (e.g. Sacks, Boxes)</span>
            <button type="button" id="cargoToggle">+ Add Details</button>
        </div>
        <input type="text" id="cargoNote" class="cargo-note hidden" name="cargo" placeholder="Tell us what cargo you are bringing">

        <button type="submit" class="btn btn-block search-btn {{ $loggedIn ? 'btn-teal' : 'btn-dark' }}">Search Available Lantsas</button>
    </form>
</section>

{{-- HOW IT WORKS --}}
<section class="container steps">
    <span class="tag">THREE SIMPLE STEPS</span>
    <h2>How SURSEA Simplifies Your Travel</h2>
    <p class="steps-lead">Say goodbye to showing up blind hoping to catch a trip. We bridge the technology gap for traditional island transportation.</p>

    <div class="step-grid">
        <article class="step-card">
            <div class="step-top"><span class="step-icon">@include('partials.icon', ['name' => 'calendar'])</span><span class="step-no">01</span></div>
            <h3>Check Live Schedules</h3>
            <p>Browse live departure times and seat availability for outriggers heading to Nonoc, Hanigad, Dinagat, and other islands directly from your phone.</p>
        </article>
        <article class="step-card">
            <div class="step-top"><span class="step-icon">@include('partials.icon', ['name' => 'clipboard'])</span><span class="step-no">02</span></div>
            <h3>Reserve Seats &amp; Log Cargo</h3>
            <p>Secure your tickets digitally. If you are bringing agricultural cargo or market bags, declare them beforehand to ensure the vessel stays within safe weight limits.</p>
        </article>
        <article class="step-card">
            <div class="step-top"><span class="step-icon">@include('partials.icon', ['name' => 'ticket'])</span><span class="step-no">03</span></div>
            <h3>Show Tickets &amp; Board</h3>
            <p>Head directly to the priority lanes at Surigao Port, present your digital QR ticket, pay the local terminal fee, and step on board the outrigger with ease.</p>
        </article>
    </div>
</section>

{{-- TODAY'S ROUTES --}}
<section class="container board-section">
    <div class="board-head">
        <div>
            <span class="tag">LIVE ISLAND CONNECTIONS</span>
            <h2>Today’s Lantsa Routes &amp; Status</h2>
            <p>All departures are monitored in coordination with local maritime bodies and the Philippine Coast Guard for safety.</p>
        </div>
        <a href="{{ route('routes') }}" class="btn btn-dark">View All Routes @include('partials.icon', ['name' => 'arrow-right'])</a>
    </div>

    @include('passenger.board-table', ['board' => $board])
</section>

@include('partials.footer')
@endsection