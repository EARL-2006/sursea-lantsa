@extends('layouts.passenger')

@section('title', 'Seat Reservation - SURSEA')

@section('content')
<div class="container narrow page form-page">
    <h1 class="page-title">Seat Reservation</h1>
    <p class="form-lead">Book your seat and cargo space for a safe and convenient journey with surigao’s lantsa services</p>

    @if(session('notice'))
        <div class="alert info">{{ session('notice') }}</div>
    @endif

    @include('partials.trip-bar', ['trip' => $trip, 'arrival' => false])

    <form class="reserve-card" method="POST" action="{{ route('seat.store', $trip['id']) }}">
        @csrf

        @if($errors->any())
            <div class="alert error-box wide">
                @foreach($errors->all() as $message)
                    <div>{{ $message }}</div>
                @endforeach
            </div>
        @endif

        <div class="reserve-col">
            <h2>Passengers Details</h2>
            <p class="hint">Enter the number of person/s who will be traveling</p>

            <div class="field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', session('user')['name']) }}" required>
            </div>
            <div class="field">
                <label for="contact">Contact Number</label>
                <input type="text" id="contact" name="contact" value="{{ old('contact', session('user')['contact']) }}" required>
            </div>
            <div class="field">
                <label for="passengers">Number of Passengers <small>({{ $trip['left'] }} seats left)</small></label>
                <input type="number" id="passengers" name="passengers" min="1" max="{{ $trip['left'] }}" value="{{ old('passengers', 1) }}" required>
            </div>

            <button type="submit" class="btn btn-dark btn-lg">Reserved Now</button>
        </div>

        <div class="reserve-col divider">
            <h2>Cargo Details (Optional)</h2>
            <p class="hint">Add Cargo Details if you have items to transport</p>

            @include('partials.cargo-rows')
        </div>
    </form>
</div>

@if(session('show_ticket') && session('reservation'))
    @include('partials.ticket', ['ticket' => session('reservation'), 'open' => true])
@endif
@endsection