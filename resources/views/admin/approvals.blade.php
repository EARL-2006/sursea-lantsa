@extends('layouts.panel')

@section('title', 'Operator Approvals - SURSEA')
@section('crumb', 'Operator Approvals')

@section('content')
<a href="{{ route('admin.dashboard') }}" class="back-link">
    @include('partials.icon', ['name' => 'arrow-left']) Operator approvals
</a>

<div class="review-title">
    <h1>Review operator</h1>
    <span class="badge amber">Pending approval</span>
</div>
<p class="subtitle">OP-2026-041 · Submitted today at 08:20</p>

<section class="box" style="margin-top: 22px;">
    <div class="box-head">
        <div>
            <h2>Operator approvals</h2>
            <p class="subtitle">Review new businesses before they go live.</p>
        </div>
        <a href="{{ route('admin.approvals') }}" class="link-arrow">
            View all {{ $pending }} @include('partials.icon', ['name' => 'arrow-right'])
        </a>
    </div>

    @include('admin.approval-table', ['applications' => $applications])
</section>
@endsection

