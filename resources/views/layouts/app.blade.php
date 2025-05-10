@php
use Illuminate\Support\Facades\Storage;
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        .avatar-dropdown {
            padding: 0;
            border: none;
            background: none;
        }
        .avatar-dropdown::after {
            display: none;
        }
    </style>
</head>
<body>
    @auth
    <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Главная</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('deals.index') }}">Сделки</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('clients.index') }}">Клиенты</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tasks.index') }}">Задачи</a>
                    </li>
                    @if(auth()->user()->isHead())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('plans.index') }}">Управление планами</a>
                        </li>
                    @endif
                </ul>
                <div class="dropdown">
                    <button class="btn avatar-dropdown" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ auth()->user()->avatar ? Storage::url(auth()->user()->avatar) : asset('storage/avatars/default_avatar.png') }}" 
                             alt="Аватар" 
                             class="rounded-circle" 
                             style="width: 40px; height: 40px; object-fit: cover;">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">Выйти</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    @endauth
    <div class="container">
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html> 