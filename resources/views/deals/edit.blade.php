@extends('layouts.app')

@section('title', 'Редактировать сделку')

@section('content')
    <div class="container">
        <h1>Редактировать сделку "{{ $deal->title }}"</h1>

        <form action="{{ route('deals.update', $deal) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="client_id" class="form-label">ID Клиента</label>
                <input type="text" class="form-control @error('client_id') is-invalid @enderror" id="client_id" name="client_id" value="{{ old('client_id', $deal->client_id) }}" disabled>
                <input type="hidden" name="client_id" value="{{ $deal->client_id }}">
                @error('client_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="title" class="form-label">Название сделки</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $deal->title) }}" required>
                @error('title')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Сумма</label>
                <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $deal->amount) }}" required min="0">
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
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ (old('user_id', $deal->user_id) == $user->id) ? 'selected' : '' }}>
                            {{ $user->full_name ?? 'Неизвестный пользователь' }}
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
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $deal->description) }}</textarea>
                @error('description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Обновить сделку</button>
        </form>
    </div>
@endsection
