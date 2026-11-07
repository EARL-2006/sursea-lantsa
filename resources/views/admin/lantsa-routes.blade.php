@extends('layouts.panel')

@section('title', 'Lantsa & Routes - SURSEA')
@section('crumb', 'Lantsa & Routes')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">ADMIN MANAGEMENT</p>
        <h1>Lantsa &amp; Routes</h1>
        <p class="subtitle">Manage registered vessels, assigned island routes, passenger capacity, and operating status.</p>
    </div>

    <div class="head-filters">
        <button type="button" class="btn btn-blue" data-toggle-box="addLantsaBox">
            @include('partials.icon', ['name' => 'plus']) Register lantsa
        </button>
    </div>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Registered lantsas</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number">32</p>
        <p class="stat-note">Across all operators</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Active routes</p>
            @include('partials.icon', ['name' => 'pin'])
        </div>
        <p class="stat-number">18</p>
        <p class="stat-note">Open for reservations</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Suspended vessels</p>
            @include('partials.icon', ['name' => 'shield'])
        </div>
        <p class="stat-number">{{ count(array_filter($lantsas, fn ($v) => $v['status'] === 'Suspended')) }}</p>
        <p class="stat-note">Not accepting trips</p>
    </div>
</div>

<section class="box hidden" id="addLantsaBox">
    <div class="box-head">
        <div>
            <h2>Register vessel &amp; route</h2>
            <p class="subtitle">Assign a verified lantsa to an approved island route.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="addLantsaBox">Close</button>
    </div>

    <form method="POST" action="{{ route('admin.lantsa.store') }}">
        @csrf
        <div class="form-two">
            <div class="form-field">
                <label for="lantsa">Lantsa / vessel name</label>
                <input type="text" id="lantsa" name="lantsa" placeholder="e.g. MV Sea Breeze" required>
            </div>
            <div class="form-field">
                <label for="operator">Operator business</label>
                <input type="text" id="operator" name="operator" placeholder="e.g. Sea Breeze Transport" required>
            </div>
        </div>

        <div class="form-two">
            <div class="form-field">
                <label for="route">Assigned route</label>
                <input type="text" id="route" name="route" placeholder="e.g. Surigao City → Dinagat" required>
            </div>
            <div class="form-field">
                <label for="seats">Passenger seat capacity</label>
                <input type="number" id="seats" name="seats" min="10" max="120" value="40" required>
            </div>
        </div>

        <button type="submit" class="btn btn-blue">Save vessel</button>
    </form>
</section>

<section class="box">
    <h2>Lantsa directory</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search lantsa, operator, or route">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Active">Active</option>
            <option value="Suspended">Suspended</option>
        </select>
        <a href="{{ route('admin.module', 'trip-schedule') }}" class="input-select export-link">View schedules</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Lantsa</th>
                    <th>Operator</th>
                    <th>Route</th>
                    <th>Passenger seats</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lantsas as $v)
                    <tr>
                        <td><strong>{{ $v['lantsa'] }}</strong></td>
                        <td>{{ $v['operator'] }}</td>
                        <td>{{ $v['route'] }}</td>
                        <td>{{ $v['seats'] }} seats</td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($v['status']) }}">{{ $v['status'] }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.lantsa.status', $v['id']) }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $v['status'] === 'Active' ? 'Suspended' : 'Active' }}">
                                <button type="submit" class="btn {{ $v['status'] === 'Active' ? 'btn-outline-red' : 'btn-blue' }} btn-sm">
                                    {{ $v['status'] === 'Active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">Showing {{ count($lantsas) }} lantsas · Example data</p>
</section>
@endsection

