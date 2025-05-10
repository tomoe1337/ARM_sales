@extends('layouts.app')

@section('title', 'Создать сделку')

@section('content')
    <div class="container">
        <h1>Создать сделку</h1>

        <form action="{{ route('deals.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="client_id" class="form-label">Клиент</label>
                <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                    <option value="">Выберите клиента</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ (old('client_id', $clientId ?? '') == $client->id) ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
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

            <button type="submit" class="btn btn-primary">Сохранить сделку</button>
        </form>
    </div>
@endsection