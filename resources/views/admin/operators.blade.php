@extends('layouts.panel')

@section('title', 'Operators - SURSEA')
@section('crumb', 'Operators')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">ADMIN MANAGEMENT</p>
        <h1>Operators</h1>
        <p class="subtitle">Manage registered businesses, account status, and vessel assignments.</p>
    </div>
    <a href="{{ route('admin.approvals') }}" class="btn btn-blue">Review applications</a>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <p class="stat-label">Active operators</p>
        <p class="stat-number">24</p>
        <p class="stat-note">Approved businesses</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Pending applications</p>
        <p class="stat-number">{{ $pending }}</p>
        <p class="stat-note">Awaiting administrator review</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Registered vessels</p>
        <p class="stat-number">32</p>
        <p class="stat-note">Across all operators</p>
    </div>
</div>

<section class="box">
    <h2>Business directory</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search business, operator, or vessel">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Active">Active</option>
            <option value="Pending">Pending</option>
            <option value="Needs info">Needs info</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
        </select>
        <a href="{{ route('admin.operators.export') }}" class="input-select export-link">Export list</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Business</th>
                    <th>Assigned vessel</th>
                    <th>Primary route</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($operators as $op)
                    <tr>
                        <td><strong>{{ $op['business'] }}</strong></td>
                        <td>{{ $op['vessel'] }}</td>
                        <td>{{ $op['route'] }}</td>
                        <td>
                            <span class="badge plain {{ \App\Support\SampleData::badge($op['status']) }}">{{ $op['status'] }}</span>
                        </td>
                        <td>
                            <a href="{{ $op['link'] }}" class="btn btn-outline btn-sm">
                                {{ $op['status'] === 'Active' ? 'Manage' : 'Review' }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">Showing {{ count($operators) }} businesses · Example data</p>
</section>
@endsection

