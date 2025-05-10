@extends('layouts.app')

@section('title', 'Создать сделку')

@section('content')
    <div class="container">
        <h1>Создать сделку</h1>

        <form action="{{ route('deals.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="client_id" class="form-label">ID Клиента</label>
                <input type="text" class="form-control @error('client_id') is-invalid @enderror" id="client_id" name="client_id" value="{{ old('client_id', $clientId ?? '') }}" required>
                @error('client_id')
 <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Название сделки</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Сумма</label>
                <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required min="0">
                @error('amount')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="user_id" class="form-label">Сотрудник</label>
                <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                    <option value="">Выберите сотрудника</option>
                    {{-- Предполагается, что у вас есть переменная $users, содержащая список сотрудников --}}
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ (old('user_id') == $user->id) ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')
 <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Описание</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
 <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Сохранить сделку</button>
        </form>
    </div>
@endsection