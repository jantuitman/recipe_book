<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="nav">
        <div class="nav-inner">
            <a href="{{ url('/') }}" class="nav-logo">{{ config('app.name') }}</a>
            <ul class="nav-links">
                @auth
                    <li><a href="{{ route('dashboard') }}" class="nav-link">My Recipes</a></li>
                    <li><a href="{{ route('chat.index') }}" class="nav-link">AI Chat</a></li>
                    <li>
                        <span class="nav-link">{{ Auth::user()->name }}</span>
                        <ul>
                            <li><a href="{{ route('settings.edit') }}" class="nav-link">Settings</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="nav-link" style="background: none; border: none; cursor: pointer; padding: 0;">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li><a href="{{ route('login') }}" class="nav-link">Login</a></li>
                    <li><a href="{{ route('register') }}" class="nav-link">Register</a></li>
                @endauth
            </ul>
        </div>
    </nav>

    <main class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
