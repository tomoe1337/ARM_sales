@extends('layouts.app')

@section('title', 'Добавление сделки')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Добавление сделки</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('deals.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Название</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Сумма</label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="new" {{ old('status') == 'new' ? 'selected' : '' }}>Новая</option>
                                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>В работе</option>
                                <option value="won" {{ old('status') == 'won' ? 'selected' : '' }}>Выиграна</option>
                                <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Проиграна</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="closed_at" class="form-label">Дата закрытия</label>
                            <input type="date" class="form-control @error('closed_at') is-invalid @enderror" id="closed_at" name="closed_at" value="{{ old('closed_at') }}">
                            @error('closed_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="client_id" class="form-label">Клиент</label>
                            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                                <option value="">Выберите клиента</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Добавить сделку</button>
                            <a href="{{ route('deals.index') }}" class="btn btn-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 