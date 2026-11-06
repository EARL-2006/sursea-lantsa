@extends('layouts.panel')

@section('title', 'Admin Dashboard - SURSEA')
@section('crumb', 'Dashboard')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">ADMIN OVERVIEW</p>
        <h1>Operations at a glance</h1>
        <p class="subtitle">Manage operators, crossings, and island connections.</p>
    </div>

    {{-- These two drop-downs are only for looks (same as the Figma) --}}
    <div class="head-filters">
        <select class="pill-select cream"><option>All routes</option><option>Surigao City → Dinagat</option><option>Surigao City → Cabungaan</option></select>
        <select class="pill-select outline"><option>Today</option><option>This week</option></select>
    </div>
</div>

<div class="stat-grid cols-4">
    <div class="stat-card">
        <div class="stat-top"><p class="stat-label">Pending Approvals</p>@include('partials.icon', ['name' => 'shield'])</div>
        <p class="stat-number">{{ $pending }}</p>
        <p class="stat-note">Applications to review</p>
    </div>
    <div class="stat-card">
        <div class="stat-top"><p class="stat-label">Active Operators</p>@include('partials.icon', ['name' => 'users'])</div>
        <p class="stat-number">24</p>
        <p class="stat-note">Approved businesses</p>
    </div>
    <div class="stat-card">
        <div class="stat-top"><p class="stat-label">Active Lantsas</p>@include('partials.icon', ['name' => 'ship'])</div>
        <p class="stat-number">32</p>
        <p class="stat-note">Registered vessels</p>
    </div>
    <div class="stat-card">
        <div class="stat-top"><p class="stat-label">Trips Today</p>@include('partials.icon', ['name' => 'calendar'])</div>
        <p class="stat-number">48</p>
        <p class="stat-note">Across all operators</p>
    </div>
</div>

{{-- Operator approvals --}}
<section class="box">
    <div class="box-head">
        <div>
            <h2>Operator approvals</h2>
            <p class="subtitle">Review new businesses before they go live.</p>
        </div>
        <a href="{{ route('admin.approvals') }}" class="link-arrow">View all {{ $pending }} @include('partials.icon', ['name' => 'arrow-right'])</a>
    </div>

    @include('admin.approval-table', ['applications' => $applications])
</section>

{{-- Bottom row --}}
<div class="two-col">
    <section class="box">
        <div class="box-head">
            <h2>Lantsa &amp; Routes</h2>
            <a href="{{ route('admin.module', 'lantsa-routes') }}" class="link-arrow" aria-label="Open Lantsa and Routes">@include('partials.icon', ['name' => 'arrow-right'])</a>
        </div>
        <div class="lantsa-list">
            @foreach($lantsas as $lantsa)
                <div class="lantsa-item">
                    <div><strong>{{ $lantsa[0] }}</strong><small>{{ $lantsa[1] }}</small></div>
                    <span class="badge {{ \App\Support\SampleData::badge($lantsa[2]) }}">{{ $lantsa[2] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="box daily-box">
        <h2>Daily report</h2>
        <p class="subtitle">A snapshot of today’s crossings.</p>
        <dl class="daily-list">
            @foreach($report as $label => $value)
                <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
            @endforeach
        </dl>
        <a href="{{ route('admin.reports.export') }}" class="btn btn-blue btn-block">Export report @include('partials.icon', ['name' => 'download'])</a>
    </section>
</div>
@endsection