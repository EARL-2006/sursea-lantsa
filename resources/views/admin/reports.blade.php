@extends('layouts.panel')

@section('title', 'Network Reports - SURSEA')
@section('crumb', 'Reports')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">ADMIN REPORTING</p>
        <h1>Network reports</h1>
        <p class="subtitle">Monitor daily crossings, passenger check-ins, and cargo across all operators.</p>
    </div>
    <a href="{{ route('admin.reports.export') }}" class="btn btn-blue">Export daily report</a>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <p class="stat-label">Completed trips</p>
        <p class="stat-number">18 / 48</p>
        <p class="stat-note">Scheduled crossings today</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Passengers checked in</p>
        <p class="stat-number">624</p>
        <p class="stat-note">Across completed trips</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Cargo registered</p>
        <p class="stat-number">86</p>
        <p class="stat-note">Items across the network</p>
    </div>
</div>

<section class="box">
    <div class="box-head">
        <h2>Route activity</h2>
        <select class="input-select small-select" aria-label="Filter route activity">
            <option>Today · All operators</option>
            <option>This week · All operators</option>
            <option>MV Isla Norte</option>
            <option>MV Marajaw</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Completed trips</th>
                    <th>Passengers</th>
                    <th>Cargo items</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalTrips = 0;
                    $totalPassengers = 0;
                    $totalCargo = 0;
                @endphp
                @foreach($routes as $row)
                    @php
                        $totalTrips += (int) $row[1];
                        $totalPassengers += (int) $row[2];
                        $totalCargo += (int) $row[3];
                    @endphp
                    <tr>
                        <td><strong>{{ $row[0] }}</strong></td>
                        <td>{{ $row[1] }}</td>
                        <td>{{ $row[2] }}</td>
                        <td>{{ $row[3] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>TOTAL</td>
                    <td>{{ $totalTrips }}</td>
                    <td>{{ $totalPassengers }}</td>
                    <td>{{ $totalCargo }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

<section class="box cream">
    <h2>Trip completion today</h2>
    <p class="subtitle">18 completed of 48 scheduled crossings</p>
    <div class="progress">
        <span style="width: 37.5%"></span>
    </div>
    <div class="progress-text">
        <strong>37.5% completed</strong>
        <span>30 trips remaining · Example data</span>
    </div>
</section>
@endsection

