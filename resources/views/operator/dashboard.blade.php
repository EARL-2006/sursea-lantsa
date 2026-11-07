@extends('layouts.panel')

@section('title', 'Operator Dashboard - SURSEA')
@section('crumb', 'Dashboard')

@section('content')
@php
    $user = session('user');
    $vesselName = $user['lantsa'] ?? 'MV Isla Norte';
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>Operations at a glance</h1>
        <p class="subtitle">Monitor today’s crossings, seat capacity, waiting list queues, and cargo for your lantsa.</p>
    </div>

    <div class="lantsa-select">
        @include('partials.icon', ['name' => 'ship'])
        <div>
            <small>ASSIGNED LANTSA</small>
            <strong>{{ $vesselName }}</strong>
        </div>
    </div>
</div>

<div class="stat-grid cols-4">
    @foreach($stats as $stat)
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">{{ $stat[0] }}</p>
                @include('partials.icon', ['name' => $stat[3]])
            </div>
            <p class="stat-number">{{ $stat[1] }}</p>
            <p class="stat-note">{{ $stat[2] }}</p>
        </div>
    @endforeach
</div>

<section class="box">
    <div class="box-head">
        <div>
            <h2>Today’s crossings</h2>
            <p class="subtitle">Live schedule and seat capacity for {{ $vesselName }}.</p>
        </div>
        <a href="{{ route('operator.module', 'trip-schedule') }}" class="link-arrow">
            Manage schedule @include('partials.icon', ['name' => 'arrow-right'])
        </a>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Port / Pier</th>
                    <th>Departure</th>
                    <th>Seats left</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trips as $trip)
                    <tr>
                        <td><strong>{{ $trip[0] }}</strong></td>
                        <td class="muted">{{ $trip[1] }}</td>
                        <td><strong>{{ $trip[2] }}</strong></td>
                        <td class="{{ $trip[3] === 'Full' || $trip[3] === '2 left' ? 'warn-text' : '' }}"><strong>{{ $trip[3] }}</strong></td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($trip[4]) }}">{{ $trip[4] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('operator.module', 'check-in') }}" class="btn btn-blue btn-sm">Check-in</a>
                                <a href="{{ route('operator.module', 'manifest') }}" class="btn btn-outline btn-sm">Manifest</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<div class="two-col">
    <section class="box">
        <div class="box-head">
            <div>
                <h2>Waiting list &amp; quick check-in</h2>
                <p class="subtitle">Passengers queued for a full crossing.</p>
            </div>
            <a href="{{ route('operator.module', 'waiting-list') }}" class="link-arrow">
                View all @include('partials.icon', ['name' => 'arrow-right'])
            </a>
        </div>

        <div class="lantsa-list">
            @foreach(array_slice(\App\Support\SampleData::waitlistRecords(), 0, 3) as $wl)
                <div class="lantsa-item">
                    <div>
                        <strong>#{{ $wl['position'] }} · {{ $wl['name'] }} ({{ $wl['passengers'] }} pax)</strong>
                        <small>{{ $wl['route'] }} · Ref {{ $wl['ref'] }}</small>
                    </div>
                    <span class="badge {{ \App\Support\SampleData::badge($wl['status']) }}">{{ $wl['status'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="box daily-box">
        <h2>Daily summary</h2>
        <p class="subtitle">Today’s operational snapshot for {{ $vesselName }}.</p>
        <dl class="daily-list">
            <div><dt>Trips completed</dt><dd>2 / 6</dd></div>
            <div><dt>Passengers checked in</dt><dd>96</dd></div>
            <div><dt>Cargo registered</dt><dd>29</dd></div>
        </dl>
        <a href="{{ route('operator.reports.export') }}" class="btn btn-blue btn-block">
            Export daily report @include('partials.icon', ['name' => 'download'])
        </a>
    </section>
</div>
@endsection

