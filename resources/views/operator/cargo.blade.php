@extends('layouts.panel')

@section('title', 'Cargo Management - Operator Portal')
@section('crumb', 'Cargo')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>Cargo</h1>
        <p class="subtitle">Record and monitor cargo assigned to MV Isla Norte across each crossing.</p>
    </div>
    <button type="button" class="btn btn-blue" data-toggle-box="opAddCargoBox">
        @include('partials.icon', ['name' => 'plus']) Record cargo
    </button>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Cargo pending</p>
            @include('partials.icon', ['name' => 'box'])
        </div>
        <p class="stat-number">14</p>
        <p class="stat-note">Items awaiting check-in</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">In transit</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number">20</p>
        <p class="stat-note">Already on board</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Delivered</p>
            @include('partials.icon', ['name' => 'check-square'])
        </div>
        <p class="stat-number">52</p>
        <p class="stat-note">Already received</p>
    </div>
</div>

<section class="box hidden" id="opAddCargoBox">
    <div class="box-head">
        <div>
            <h2>Record cargo for MV Isla Norte</h2>
            <p class="subtitle">Log cargo packages brought by senders or passengers at the port.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="opAddCargoBox">Close</button>
    </div>

    <form method="POST" action="{{ route('operator.cargo.store') }}">
        @csrf
        <div class="form-two">
            <div class="form-field">
                <label for="sender">Sender name</label>
                <input type="text" id="sender" name="sender" placeholder="e.g. Robert Zabala" required>
            </div>
            <div class="form-field">
                <label for="receiver">Receiver name</label>
                <input type="text" id="receiver" name="receiver" placeholder="e.g. Earl Fermillan" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="items">Cargo items</label>
                <input type="text" id="items" name="items" placeholder="e.g. Box of Goods, Sack of Rice" required>
            </div>
            <div class="form-field">
                <label for="qty">Quantity</label>
                <input type="number" id="qty" name="qty" min="1" max="99" value="2" required>
            </div>
        </div>
        <button type="submit" class="btn btn-blue">Save cargo entry</button>
    </form>
</section>

<section class="box">
    <h2>Cargo list</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search tracking ID, sender, receiver, or item">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Pending">Pending</option>
            <option value="In transit">In transit</option>
            <option value="Delivered">Delivered</option>
        </select>
        <a href="{{ route('operator.reports.export') }}" class="input-select export-link">Export report</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Tracking ID</th>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>Items</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cargos as $cargo)
                    <tr>
                        <td><strong>{{ $cargo['ref'] }}</strong></td>
                        <td>{{ $cargo['sender'] }}</td>
                        <td>{{ $cargo['receiver'] }}</td>
                        <td>{{ $cargo['items'] }}</td>
                        <td><strong>{{ $cargo['qty'] }}</strong></td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($cargo['status']) }}">{{ $cargo['status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                @if($cargo['status'] === 'Pending')
                                    <form method="POST" action="{{ route('operator.cargo.status', $cargo['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="In transit">
                                        <button type="submit" class="btn btn-blue btn-sm">Load</button>
                                    </form>
                                @elseif($cargo['status'] === 'In transit')
                                    <form method="POST" action="{{ route('operator.cargo.status', $cargo['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="Delivered">
                                        <button type="submit" class="btn btn-blue btn-sm">Delivered</button>
                                    </form>
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-preview-ticket="cargo"
                                    data-ref="{{ $cargo['ref'] }}"
                                    data-sender="{{ $cargo['sender'] }}"
                                    data-sender-contact="{{ $cargo['sender_contact'] }}"
                                    data-receiver="{{ $cargo['receiver'] }}"
                                    data-receiver-contact="{{ $cargo['receiver_contact'] }}"
                                    data-items="{{ $cargo['items'] }}"
                                    data-qty="{{ $cargo['qty'] }}"
                                    data-lantsa="{{ $cargo['lantsa'] }}"
                                >Ticket</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">Showing {{ count($cargos) }} deliveries · Example data</p>
</section>

@include('partials.panel-ticket-modal')
@endsection

