@extends('layouts.panel')

@section('title', 'My Vessel & Routes - SURSEA')
@section('crumb', 'My Vessel & Routes')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>My Vessel &amp; Routes</h1>
        <p class="subtitle">Your registered lantsa, passenger capacity, and the island routes approved by the administrator.</p>
    </div>
    <a href="{{ route('operator.module', 'trip-schedule') }}" class="btn btn-blue">Manage trip schedule</a>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Vessel</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number small">MV Isla Norte</p>
        <p class="stat-note">Passenger &amp; cargo outrigger</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Passenger seats</p>
            @include('partials.icon', ['name' => 'armchair'])
        </div>
        <p class="stat-number">40</p>
        <p class="stat-note">Approved total capacity</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved routes</p>
            @include('partials.icon', ['name' => 'pin'])
        </div>
        <p class="stat-number">4</p>
        <p class="stat-note">Active island connections</p>
    </div>
</div>

<section class="box">
    <h2>My approved routes</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search route or port">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Active">Active</option>
        </select>
        <a href="{{ route('operator.module', 'trip-schedule') }}" class="input-select export-link">View trips</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Via port / pier</th>
                    <th>Passenger fare</th>
                    <th>Cargo fee</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach([
                    ['Surigao City → Dinagat', 'Surigao City Port', '₱150', '₱25 / item', 'Active'],
                    ['Surigao City → Cabungaan', 'San Jose Wharf', '₱150', '₱25 / item', 'Active'],
                    ['Surigao City → Cagdianao', 'Cagdianao Pier', '₱150', '₱25 / item', 'Active'],
                    ['Surigao City → Del Carmen', 'Del Carmen Port', '₱150', '₱25 / item', 'Active'],
                ] as $route)
                    <tr>
                        <td><strong>{{ $route[0] }}</strong></td>
                        <td>{{ $route[1] }}</td>
                        <td><strong>{{ $route[2] }}</strong></td>
                        <td>{{ $route[3] }}</td>
                        <td><span class="badge green">{{ $route[4] }}</span></td>
                        <td>
                            <a href="{{ route('operator.module', 'trip-schedule') }}" class="btn btn-outline btn-sm">Schedule</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">Showing 4 routes · Example data</p>
</section>
@endsection

