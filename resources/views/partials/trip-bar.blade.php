<div class="trip-bar">
    <div class="trip-bar-item">
        <span class="circle">@include('partials.icon', ['name' => 'pin'])</span>
        <div>
            <small>Selected Trip</small>
            <strong>{{ $trip['from'] }} → {{ $trip['to'] }}</strong>
        </div>
    </div>

    <div class="trip-bar-item">
        <span class="circle">@include('partials.icon', ['name' => 'ship'])</span>
        <div>
            <small>Lantsa’s name</small>
            <strong>{{ $trip['lantsa'] }}</strong>
        </div>
    </div>

    @if(!empty($arrival))
        <div class="trip-bar-item">
            <span class="circle">@include('partials.icon', ['name' => 'clock'])</span>
            <div>
                <small>Arrival Time</small>
                <strong>{{ $trip['arrival'] }}</strong>
            </div>
        </div>
    @endif

    <div class="trip-bar-item">
        <span class="circle">@include('partials.icon', ['name' => 'ship'])</span>
        <div>
            <small>Departure</small>
            <strong>{{ $trip['date_long'] }} - {{ $trip['departure'] }}</strong>
        </div>
    </div>
</div>

