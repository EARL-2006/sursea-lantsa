<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SURSEA Portal')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>
<body class="panel-body">
@php
    // This one layout is used by BOTH the admin portal and the operator portal.
    $user = session('user');
    $isAdmin = $user['role'] === 'admin';
    $menu = \App\Support\SampleData::menu($user['role']);
    $homeUrl = url($isAdmin ? 'admin/dashboard' : 'operator/dashboard');
@endphp

<div class="panel">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="sidebar">
        <a href="{{ $homeUrl }}" class="brand">@include('partials.logo')</a>

        <div class="workspace-card">
            @if($isAdmin)
                <small>ADMIN WORKSPACE</small>
                <strong>SURSEA Administration</strong>
                <span>@include('partials.icon', ['name' => 'shield']) Surigao City</span>
            @else
                <small>YOUR BUSINESS</small>
                <strong>{{ $user['business'] }}</strong>
                <span>@include('partials.icon', ['name' => 'ship']) {{ $user['lantsa'] }}</span>
            @endif
        </div>

        <nav class="side-nav">
            @foreach($menu as $group)
                <p class="side-label">{{ $group['label'] }}</p>
                @foreach($group['items'] as $item)
                    <a href="{{ url($item['path']) }}" class="side-link {{ request()->is($item['path'].'*') ? 'active' : '' }}">
                        @include('partials.icon', ['name' => $item['icon']])
                        <span>{{ $item['label'] }}</span>
                        @if($item['badge'])
                            <em class="count">{{ $item['badge'] }}</em>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="side-link">@include('partials.icon', ['name' => 'logout']) <span>Log out</span></button>
        </form>
    </aside>

    {{-- ============ MAIN AREA ============ --}}
    <div class="panel-main">
        <header class="topbar">
            <div class="crumbs">
                <strong>{{ $isAdmin ? 'Admin Portal' : 'Operator Portal' }}</strong>
                <span>/</span>
                <span>@yield('crumb')</span>
            </div>

            <div class="topbar-right">
                <span class="top-text">{{ $isAdmin ? 'Surigao City' : $user['lantsa'] }}</span>

                <a href="{{ url($isAdmin ? 'admin/notifications' : 'operator/notifications') }}" class="bell-btn" aria-label="Notifications">
                    @include('partials.icon', ['name' => 'bell'])
                </a>

                <div class="dropdown">
                    <button type="button" class="user-btn" data-dropdown-btn="panelUserMenu">
                        @include('partials.icon', ['name' => 'user'])
                        <span>{{ $isAdmin ? 'Administrator' : 'Operator' }}</span>
                        @include('partials.icon', ['name' => 'chevron-down'])
                    </button>
                    <div class="dropdown-menu" id="panelUserMenu">
                        <p class="menu-title">{{ $user['name'] }}</p>
                        <p class="menu-text">{{ $user['email'] }}</p>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="menu-item">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="panel-content">
            @if(session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>

        @if($isAdmin)
            <footer class="mini-footer">
                <span>© 2026 SURSEA · Surigao City Maritime Development · Example data</span>
                <span><a href="#">Privacy Policy</a> · <a href="#">Terms of Service</a></span>
            </footer>
        @else
            @include('partials.footer')
        @endif
    </div>
</div>

<script src="{{ asset('js/common.js') }}"></script>
<script src="{{ asset('js/panel.js') }}"></script>
</body>
</html>
