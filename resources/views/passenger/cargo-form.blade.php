@extends('layouts.passenger')

@section('title', 'Cargo Reservation - SURSEA')

@section('content')
<div class="container narrow page form-page">
    <h1 class="page-title">Cargo Reservation</h1>
    <p class="form-lead">Reserve space for your cargo before your trip. Check available schedules, cargo capacity, and booking status to ensure your goods can be accommodated on your selected lantsa trip.</p>

    @include('partials.trip-bar', ['trip' => $trip, 'arrival' => false])

    <form class="reserve-card" method="POST" action="{{ route('cargo.store', $trip['id']) }}">
        @csrf

        @if($errors->any())
            <div class="alert error-box wide">
                @foreach($errors->all() as $message)
                    <div>{{ $message }}</div>
                @endforeach
            </div>
        @endif

        <div class="reserve-col">
            <h2 class="with-icon">@include('partials.icon', ['name' => 'user-circle']) Sender &amp; Receiver Details</h2>
            <p class="hint">Enter the details of the sender or receiver responsible for the cargo.</p>

            <div class="field">
                <label for="sender_name">Sender Name</label>
                <input type="text" id="sender_name" name="sender_name" value="{{ old('sender_name', session('user')['name']) }}" required>
            </div>
            <div class="field">
                <label for="sender_contact">Sender Contact Number</label>
                <input type="text" id="sender_contact" name="sender_contact" value="{{ old('sender_contact', session('user')['contact']) }}" required>
            </div>
            <div class="field">
                <label for="receiver_name">Receiver Name</label>
                <input type="text" id="receiver_name" name="receiver_name" value="{{ old('receiver_name', 'Earl Fermillan') }}" required>
            </div>
            <div class="field">
                <label for="receiver_contact">Receiver Contact Number</label>
                <input type="text" id="receiver_contact" name="receiver_contact" value="{{ old('receiver_contact', '09123123123') }}" required>
            </div>

            <button type="submit" class="btn btn-dark btn-lg">Reserved Delivery Now</button>
        </div>

        <div class="reserve-col divider">
            <h2 class="with-icon">@include('partials.icon', ['name' => 'box']) Cargo Delivery Details</h2>
            <p class="hint">Add Cargo Details items to transport</p>

            @include('partials.cargo-rows', ['defaults' => [['name' => 'Box of Coca Cola', 'qty' => 5], ['name' => 'Box of Sprite', 'qty' => 4]]])
        </div>
    </form>
</div>

@if(session('show_ticket') && session('cargo'))
    @include('partials.ticket', ['ticket' => session('cargo'), 'open' => true])
@endif
@endsection

