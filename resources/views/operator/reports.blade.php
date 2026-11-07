@extends('layouts.panel')

@section('title', 'Operator Reports - SURSEA')
@section('crumb', 'Reports')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>Reports</h1>
        <p class="subtitle">Daily crossing summary, passenger check-ins, and cargo totals for MV Isla Norte.</p>
    </div>
    <a href="{{ route('operator.reports.export') }}" class="btn btn-blue">Export daily report</a>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <p class="stat-label">Completed trips</p>
        <p class="stat-number">2 / 6</p>
        <p class="stat-note">Scheduled crossings today</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Passengers checked in</p>
        <p class="stat-number">96</p>
        <p class="stat-note">Across completed trips</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Cargo registered</p>
        <p class="stat-number">29</p>
        <p class="stat-note">Items today</p>
    </div>
</div>

<section class="box">
    <div class="box-head">
        <h2>Route activity</h2>
        <select class="input-select small-select" aria-label="Filter period">
            <option>Today · MV Isla Norte</option>
            <option>This week · MV Isla Norte</option>
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
                <tr>
                    <td><strong>Surigao City → Dinagat</strong></td>
                    <td>1</td>
                    <td>40</td>
                    <td>12</td>
                </tr>
                <tr>
                    <td><strong>Surigao City → Cabungaan</strong></td>
                    <td>1</td>
                    <td>56</td>
                    <td>17</td>
                </tr>
                <tr>
                    <td><strong>Surigao City → Cagdianao</strong></td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                </tr>
                <tr>
                    <td><strong>Surigao City → Del Carmen</strong></td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>TOTAL</td>
                    <td>2</td>
                    <td>96</td>
                    <td>29</td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

<section class="box cream">
    <h2>Trip completion today</h2>
    <p class="subtitle">2 completed of 6 scheduled crossings for MV Isla Norte</p>
    <div class="progress">
        <span style="width: 33.3%"></span>
    </div>
    <div class="progress-text">
        <strong>33.3% completed</strong>
        <span>4 trips remaining · Example data</span>
    </div>
</section>
@endsection

