@extends('layouts.panel')

@section('title', 'Workspace Settings - SURSEA')
@section('crumb', 'Settings')

@section('content')
<form method="POST" action="{{ route('admin.settings.save') }}">
    @csrf

    <div class="page-head">
        <div>
            <p class="eyebrow">ADMIN CONFIGURATION</p>
            <h1>Workspace settings</h1>
            <p class="subtitle">Configure workspace information, role permissions, and approval policies.</p>
        </div>
        <button type="submit" class="btn btn-blue">Save changes</button>
    </div>

    <div class="stat-grid cols-3">
        <div class="stat-card">
            <p class="stat-label">Administrator</p>
            <p class="stat-number small">Full oversight</p>
            <p class="stat-note">Accounts, approvals, and network</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Operator</p>
            <p class="stat-number small">Own business only</p>
            <p class="stat-note">Trips, manifests, and cargo</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Passenger</p>
            <p class="stat-number small">Personal access</p>
            <p class="stat-note">Reservations and tickets</p>
        </div>
    </div>

    <div class="settings-grid">
        <section class="box">
            <h2>Workspace information</h2>

            <div class="form-field">
                <label for="workspace_name">Workspace name</label>
                <input type="text" id="workspace_name" name="workspace_name" value="{{ old('workspace_name', $settings['workspace_name']) }}" required>
            </div>

            <div class="form-two">
                <div class="form-field">
                    <label for="service_area">Service area</label>
                    <input type="text" id="service_area" name="service_area" value="{{ old('service_area', $settings['service_area']) }}" required>
                </div>
                <div class="form-field">
                    <label for="timezone">Timezone</label>
                    <input type="text" id="timezone" name="timezone" value="{{ old('timezone', $settings['timezone']) }}" required>
                </div>
            </div>

            <div class="form-field">
                <label for="support_contact">Support contact</label>
                <input type="email" id="support_contact" name="support_contact" value="{{ old('support_contact', $settings['support_contact']) }}" required>
            </div>

            <p class="table-note">These details appear in administrative communications.</p>
        </section>

        <section class="box">
            <h2>Registration policies</h2>
            <div class="policy-list">
                <div class="policy">
                    <strong>Operator registration</strong>
                    <span>Admin approval required</span>
                </div>
                <div class="policy">
                    <strong>Required documents</strong>
                    <span>Permit, ID, vessel registration</span>
                </div>
                <div class="policy">
                    <strong>Passenger registration</strong>
                    <span>Self-service account creation</span>
                </div>
                <div class="policy">
                    <strong>Route &amp; vessel changes</strong>
                    <span>Administrator review required</span>
                </div>
            </div>
        </section>
    </div>

    <section class="box cream">
        <h2>Keep access appropriate to each role</h2>
        <p class="subtitle big-text">Administrators manage accounts and network configuration. Operators manage only their assigned business. Passengers can view and manage only their own reservations and tickets.</p>
    </section>
</form>
@endsection

