@extends('layouts.panel')

@section('title', 'Trip Schedule - SURSEA')
@section('crumb', 'Trip Schedule')

@section('content')
@php
    $completedCount = count(array_filter($trips, fn ($t) => $t['status'] === 'Completed'));
    $activeCount = count(array_filter($trips, fn ($t) => in_array($t['status'], ['Boarding', 'On Going'])));
    $scheduledCount = count(array_filter($trips, fn ($t) => $t['status'] === 'Scheduled'));
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">ADMIN MANAGEMENT</p>
        <h1>Trip schedule</h1>
        <p class="subtitle">Manage scheduled crossings, seat capacity, and departure status across all operators.</p>
    </div>

    <div class="head-filters">
        <button type="button" class="btn btn-blue" data-toggle-box="addTripBox">
            @include('partials.icon', ['name' => 'plus']) Schedule crossing
        </button>
        <a href="{{ route('admin.trips.export') }}" class="btn btn-outline">
            Export schedule @include('partials.icon', ['name' => 'download'])
        </a>
    </div>
</div>

<div class="stat-grid cols-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Trips today</p>
            @include('partials.icon', ['name' => 'calendar'])
        </div>
        <p class="stat-number">{{ max(48, count($trips)) }}</p>
        <p class="stat-note">Scheduled crossings</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Completed</p>
            @include('partials.icon', ['name' => 'check-square'])
        </div>
        <p class="stat-number">{{ max(18, $completedCount) }}</p>
        <p class="stat-note">Already arrived at port</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Boarding &amp; active</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number">{{ max(12, $activeCount) }}</p>
        <p class="stat-note">Boarding or in transit</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Remaining</p>
            @include('partials.icon', ['name' => 'clock'])
        </div>
        <p class="stat-number">{{ max(30, $scheduledCount) }}</p>
        <p class="stat-note">Still to depart today</p>
    </div>
</div>

{{-- Add new trip schedule form box (toggled by button) --}}
<section class="box hidden" id="addTripBox">
    <div class="box-head">
        <div>
            <h2>Schedule a new crossing</h2>
            <p class="subtitle">Assign a registered lantsa, route, departure time, and seat capacity.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="addTripBox">Close</button>
    </div>

    <form method="POST" action="{{ route('admin.trips.store') }}">
        @csrf
        <div class="form-two">
            <div class="form-field">
                <label for="lantsa">Assigned lantsa</label>
                <select id="lantsa" name="lantsa" class="input-select" style="width: 100%;" required>
                    @foreach($lantsas as $v)
                        <option value="{{ $v['lantsa'] }}|{{ $v['operator'] }}|{{ $v['seats'] }}">{{ $v['lantsa'] }} ({{ $v['operator'] }} · {{ $v['seats'] }} seats)</option>
                    @endforeach
                </select>
            </div>
            <div class="form-field">
                <label for="route">Crossing route</label>
                <select id="route" name="route" class="input-select" style="width: 100%;" required>
                    <option value="Surigao City → Dinagat|Surigao City Port">Surigao City → Dinagat (Surigao City Port)</option>
                    <option value="Dinagat → Surigao City|Dinagat Port">Dinagat → Surigao City (Dinagat Port)</option>
                    <option value="Surigao City → Cabungaan|San Jose Wharf">Surigao City → Cabungaan (San Jose Wharf)</option>
                    <option value="Surigao City → Cagdianao|Cagdianao Pier">Surigao City → Cagdianao (Cagdianao Pier)</option>
                    <option value="Surigao City → Del Carmen|Del Carmen Port">Surigao City → Del Carmen (Del Carmen Port)</option>
                    <option value="Surigao City → Libjo|Libjo Port">Surigao City → Libjo (Libjo Port)</option>
                    <option value="Surigao City → Hikdop Island|Hikdop Wharf">Surigao City → Hikdop Island (Hikdop Wharf)</option>
                </select>
            </div>
        </div>

        <div class="form-two">
            <div class="form-field">
                <label for="departure">Departure time</label>
                <input type="text" id="departure" name="departure" placeholder="e.g. 02:30 PM" value="02:30 PM" required>
            </div>
            <div class="form-field">
                <label for="status">Initial trip status</label>
                <select id="status" name="status" class="input-select" style="width: 100%;" required>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Boarding">Boarding</option>
                    <option value="On Going">On Going</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-blue">Save trip schedule</button>
    </form>
</section>

<section class="box">
    <div class="box-head">
        <div>
            <h2>Today’s crossings</h2>
            <p class="subtitle">Monitor live seat availability, boarding progress, and departure status.</p>
        </div>
        <a href="{{ route('admin.module', 'passenger-manifest') }}" class="link-arrow">
            Open passenger manifest @include('partials.icon', ['name' => 'arrow-right'])
        </a>
    </div>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search route, lantsa, operator, or departure time">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Completed">Completed</option>
            <option value="On Going">On Going</option>
            <option value="Boarding">Boarding</option>
            <option value="Scheduled">Scheduled</option>
            <option value="Cancelled">Cancelled</option>
        </select>
        <a href="{{ route('admin.trips.export') }}" class="input-select export-link">Export list</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Departure</th>
                    <th>Route</th>
                    <th>Lantsa &amp; operator</th>
                    <th>Seat capacity</th>
                    <th>Seats left</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trips as $trip)
                    <tr>
                        <td>
                            <strong>{{ $trip['departure'] }}</strong>
                            <br>
                            <small class="muted">{{ $trip['date'] }}</small>
                        </td>
                        <td>
                            <strong>{{ $trip['route'] }}</strong>
                            <br>
                            <small class="muted">{{ $trip['port'] }}</small>
                        </td>
                        <td>
                            <strong>{{ $trip['lantsa'] }}</strong>
                            <br>
                            <small class="muted">{{ $trip['operator'] }}</small>
                        </td>
                        <td>
                            <strong>{{ $trip['reserved'] }} / {{ $trip['capacity'] }}</strong>
                            <br>
                            <small class="muted">{{ $trip['cargo_count'] }} cargo items</small>
                        </td>
                        <td>
                            <strong class="{{ $trip['seats_left'] === 'Full' ? 'warn-text' : '' }}">{{ $trip['seats_left'] }}</strong>
                        </td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($trip['status']) }}">{{ $trip['status'] }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.trips.status', $trip['id']) }}" class="inline-action-form">
                                @csrf
                                <select name="status" class="input-select compact-select" aria-label="Change trip status" onchange="this.form.submit()">
                                    @foreach(['Scheduled', 'Boarding', 'On Going', 'Completed', 'Cancelled'] as $st)
                                        <option value="{{ $st }}" @selected($trip['status'] === $st)>{{ $st }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('admin.module', 'passenger-manifest') }}?search={{ urlencode($trip['lantsa']) }}" class="btn btn-outline btn-sm">Manifest</a>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">Showing {{ count($trips) }} of 48 trips · Example data</p>
</section>

<section class="box cream">
    <h2>Trip completion &amp; seat capacity monitoring</h2>
    <p class="subtitle">18 completed of 48 scheduled crossings · Seat availability updates automatically as passengers reserve online</p>
    <div class="progress">
        <span style="width: 37.5%"></span>
    </div>
    <div class="progress-text">
        <strong>37.5% completed</strong>
        <span>30 trips remaining · Coast Guard monitored</span>
    </div>
</section>
@endsection

