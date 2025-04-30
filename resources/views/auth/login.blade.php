@extends('layouts.app')

@section('title', 'Авторизация')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Авторизация</div>
            <div class="card-body">
                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="login" class="form-label">Логин</label>
                        <input type="text" class="form-control @error('login') is-invalid @enderror" id="login" name="login" required>
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Войти</button>
                </form>
                <div class="mt-3">
                    <p>У вас нет аккаунта? - <a href="{{ route('register') }}">зарегистрируйтесь</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 