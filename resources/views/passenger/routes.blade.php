@extends('layouts.passenger')

@section('title', 'Available Routes - SURSEA')

@section('content')
<div class="container page">
    <p class="eyebrow dark">ALL CROSSINGS</p>
    <h1 class="page-title">Available Routes</h1>
    <p class="lead small">Stops and fares for every lantsa crossing we operate.</p>

    @include('passenger.board-table', ['board' => $board])
</div>

@include('partials.footer')
@endsection

