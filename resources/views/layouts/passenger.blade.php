<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SURSEA Lantsa Hub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/passenger.css') }}">
</head>
<body>
@php
    $user = session('user');
    $isPassenger = $user && $user['role'] === 'passenger';
@endphp

<header class="site-header">
    <div class="header-inner">
        <a href="{{ route('home') }}" class="brand">@include('partials.logo')</a>

        <nav class="main-nav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('schedules') }}" class="{{ request()->routeIs('schedules', 'seat.*', 'cargo.*') ? 'active' : '' }}">Schedules</a>
            <a href="{{ route('waiting-list') }}" class="{{ request()->routeIs('waiting-list', 'waitlist.*') ? 'active' : '' }}">Waiting List</a>
            <a href="{{ route('routes') }}" class="{{ request()->routeIs('routes') ? 'active' : '' }}">Routes</a>
        </nav>

        <div class="header-actions">
            {{-- Bell --}}
            <div class="dropdown">
                <button type="button" class="icon-btn" data-dropdown-btn="bellMenu" aria-label="Notifications">
                    @include('partials.icon', ['name' => 'bell'])
                    <span class="bell-dot"></span>
                </button>
                <div class="dropdown-menu" id="bellMenu">
                    <p class="menu-title">Notifications</p>
                    @if(session('reservation') && $isPassenger)
                        <p class="menu-text">Your reservation {{ session('reservation')['ref'] }} is confirmed.</p>
                    @endif
                    @if(session('waitlist') && $isPassenger)
                        <p class="menu-text">You joined the waiting list for {{ session('waitlist')['trip']['from'] }} → {{ session('waitlist')['trip']['to'] }}.</p>
                    @endif
                    @if(!$isPassenger)
                        <p class="menu-text">Log in to see your notifications.</p>
                    @elseif(!session('reservation') && !session('waitlist'))
                        <p class="menu-text">No new notifications.</p>
                    @endif
                </div>
            </div>

            {{-- Profile (logged in) or Login button (guest) --}}
            @if($isPassenger)
                <div class="dropdown">
                    <button type="button" class="icon-btn" data-dropdown-btn="userMenu" aria-label="Account">
                        @include('partials.icon', ['name' => 'user-circle'])
                    </button>
                    <div class="dropdown-menu" id="userMenu">
                        <p class="menu-title">{{ $user['name'] }}</p>
                        <p class="menu-text">Passenger account</p>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="menu-item">Log out</button>
                        </form>
                    </div>
                </div>
            @elseif($user)
                {{-- An operator or admin is looking at the public pages --}}
                <a href="{{ url(\App\Support\SampleData::homeFor($user['role'])) }}" class="btn btn-dark">Go to portal</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-dark login-btn">login</a>
            @endif
        </div>
    </div>
</header>

<main>
    @if(session('status'))
        <div class="container"><div class="alert success">{{ session('status') }}</div></div>
    @endif

    @yield('content')
</main>

<script src="{{ asset('js/common.js') }}"></script>
<script src="{{ asset('js/passenger.js') }}"></script>
</body>
</html>
