@extends('layouts.panel')

@section('title', 'Passenger Manifest - SURSEA')
@section('crumb', 'Passenger Manifest')

@section('content')
@php
    $checkedInCount = count(array_filter($manifests, fn ($m) => $m['status'] === 'Checked in'));
    $reservedCount = count(array_filter($manifests, fn ($m) => $m['status'] === 'Reserved'));
    $waitingCount = count(array_filter($manifests, fn ($m) => $m['status'] === 'Waiting'));
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">ADMIN MANAGEMENT</p>
        <h1>Passenger manifest</h1>
        <p class="subtitle">Check the passenger list, verify ticket reference codes, and manage waiting list promotions before each crossing departs.</p>
    </div>

    <div class="head-filters">
        <button type="button" class="btn btn-blue" data-toggle-box="addManifestBox">
            @include('partials.icon', ['name' => 'plus']) Add passenger
        </button>
        <a href="{{ route('admin.manifest.export') }}" class="btn btn-outline">
            Export manifest @include('partials.icon', ['name' => 'download'])
        </a>
    </div>
</div>

<div class="stat-grid cols-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Checked in</p>
            @include('partials.icon', ['name' => 'check-square'])
        </div>
        <p class="stat-number">{{ max(624, $checkedInCount) }}</p>
        <p class="stat-note">Verified &amp; ready to board</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Reserved</p>
            @include('partials.icon', ['name' => 'ticket'])
        </div>
        <p class="stat-number">{{ max(88, $reservedCount) }}</p>
        <p class="stat-note">Waiting to check in</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">On waiting list</p>
            @include('partials.icon', ['name' => 'waiting'])
        </div>
        <p class="stat-number">{{ max(9, $waitingCount) }}</p>
        <p class="stat-note">Passengers in queue</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total passengers</p>
            @include('partials.icon', ['name' => 'users'])
        </div>
        <p class="stat-number">{{ 624 + 88 + count($manifests) }}</p>
        <p class="stat-note">Across today’s crossings</p>
    </div>
</div>

{{-- Quick reference verification bar --}}
<section class="box">
    <div class="box-head">
        <div>
            <h2>Verify ticket reference code</h2>
            <p class="subtitle">Enter a passenger’s reservation reference code (e.g. 0000-00003) to verify and check in at the terminal.</p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.manifest.verify') }}" class="verify-bar">
        @csrf
        <input type="text" name="ref" class="input-search" placeholder="Enter reservation reference code (e.g. 0000-00003)" required>
        <button type="submit" class="btn btn-blue">
            @include('partials.icon', ['name' => 'check']) Verify &amp; check in
        </button>
    </form>
</section>

{{-- Add walk-in / manual passenger entry form box --}}
<section class="box hidden" id="addManifestBox">
    <div class="box-head">
        <div>
            <h2>Add passenger to manifest</h2>
            <p class="subtitle">Log a walk-in passenger or manual port reservation onto an active crossing.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="addManifestBox">Close</button>
    </div>

    <form method="POST" action="{{ route('admin.manifest.store') }}">
        @csrf
        <div class="form-two">
            <div class="form-field">
                <label for="name">Passenger full name</label>
                <input type="text" id="name" name="name" placeholder="e.g. Juan Dela Cruz" required>
            </div>
            <div class="form-field">
                <label for="contact">Contact number</label>
                <input type="text" id="contact" name="contact" placeholder="e.g. 09171234567" required>
            </div>
        </div>

        <div class="form-two">
            <div class="form-field">
                <label for="route">Crossing route</label>
                <select id="route" name="route" class="input-select" style="width: 100%;" required>
                    <option value="Surigao City → Dinagat">Surigao City → Dinagat</option>
                    <option value="Dinagat → Surigao City">Dinagat → Surigao City</option>
                    <option value="Surigao City → Cabungaan">Surigao City → Cabungaan</option>
                    <option value="Surigao City → Cagdianao">Surigao City → Cagdianao</option>
                    <option value="Surigao City → Del Carmen">Surigao City → Del Carmen</option>
                    <option value="Libjo → Surigao City">Libjo → Surigao City</option>
                    <option value="San Jose → Surigao City">San Jose → Surigao City</option>
                </select>
            </div>
            <div class="form-field">
                <label for="lantsa">Assigned lantsa</label>
                <select id="lantsa" name="lantsa" class="input-select" style="width: 100%;" required>
                    <option value="MV Isla Norte">MV Isla Norte</option>
                    <option value="MV Marajaw">MV Marajaw</option>
                    <option value="M/B Nonoc Express">M/B Nonoc Express</option>
                    <option value="M/B Alona Waves">M/B Alona Waves</option>
                    <option value="M/B Island Voyager">M/B Island Voyager</option>
                </select>
            </div>
        </div>

        <div class="form-two">
            <div class="form-field">
                <label for="passengers">Number of passengers</label>
                <input type="number" id="passengers" name="passengers" min="1" max="20" value="1" required>
            </div>
            <div class="form-field">
                <label for="status">Boarding status</label>
                <select id="status" name="status" class="input-select" style="width: 100%;" required>
                    <option value="Checked in">Checked in</option>
                    <option value="Reserved">Reserved</option>
                    <option value="Waiting">Waiting</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-blue">Add to manifest</button>
    </form>
</section>

<section class="box">
    <div class="box-head">
        <div>
            <h2>Passenger list</h2>
            <p class="subtitle">Official manifest records across all scheduled crossings.</p>
        </div>
    </div>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" value="{{ request('search') }}" placeholder="Search passenger, reference code, route, or lantsa">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Checked in">Checked in</option>
            <option value="Reserved">Reserved</option>
            <option value="Waiting">Waiting</option>
            <option value="Moved to seat">Moved to seat</option>
        </select>
        <a href="{{ route('admin.manifest.export') }}" class="input-select export-link">Export list</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Passenger</th>
                    <th>Route</th>
                    <th>Lantsa</th>
                    <th>Passengers</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($manifests as $row)
                    <tr>
                        <td><strong>{{ $row['ref'] }}</strong></td>
                        <td>
                            <strong>{{ $row['name'] }}</strong>
                            <br>
                            <small class="muted">{{ $row['contact'] }}</small>
                        </td>
                        <td>
                            <strong>{{ $row['route'] }}</strong>
                            <br>
                            <small class="muted">{{ $row['departure'] }} · {{ $row['date'] }}</small>
                        </td>
                        <td>{{ $row['lantsa'] }}</td>
                        <td>
                            <strong>{{ $row['passengers'] }}</strong>
                            @if(!empty($row['cargo_qty']))
                                <br><small class="muted">{{ $row['cargo_qty'] }} cargo · ₱{{ $row['total'] }}</small>
                            @else
                                <br><small class="muted">₱{{ $row['total'] }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($row['status']) }}">{{ $row['status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                @if($row['status'] === 'Reserved')
                                    <form method="POST" action="{{ route('admin.manifest.status', $row['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="Checked in">
                                        <button type="submit" class="btn btn-blue btn-sm">Check in</button>
                                    </form>
                                @elseif($row['status'] === 'Waiting')
                                    <form method="POST" action="{{ route('admin.manifest.status', $row['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="Moved to seat">
                                        <button type="submit" class="btn btn-blue btn-sm">Move to seat</button>
                                    </form>
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-preview-ticket="passenger"
                                    data-ref="{{ $row['ref'] }}"
                                    data-name="{{ $row['name'] }}"
                                    data-passengers="{{ $row['passengers'] }}"
                                    data-cargo="{{ $row['cargo_qty'] }}"
                                    data-route="{{ $row['route'] }}, {{ $row['date'] }}, {{ $row['departure'] }}"
                                    data-lantsa="{{ $row['lantsa'] }}"
                                    data-contact="{{ $row['contact'] }}"
                                    data-total="{{ $row['total'] }}"
                                >Ticket</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">Showing {{ count($manifests) }} passengers · Example data</p>
</section>

<section class="box cream">
    <h2>Port boarding &amp; Coast Guard manifest compliance</h2>
    <p class="subtitle big-text">All passengers must present a valid reservation reference code or digital QR ticket at Surigao Port check-in before boarding. Waitlisted passengers are automatically notified when a seat becomes available.</p>
</section>

@include('partials.panel-ticket-modal')
@endsection

