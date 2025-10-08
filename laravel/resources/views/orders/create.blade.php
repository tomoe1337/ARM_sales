@extends('layouts.app')

@section('title', 'Создать заказ')

@section('content')
    <div class="container">
        @isset($selectedClient)
            <h1>Создать заказ с клиентом {{ $selectedClient->name }}</h1>
        @else
            <h1>Создать новый заказ</h1>
        @endisset

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="bluesales_id" class="form-label">ID BlueSales</label>
                <input type="text" class="form-control @error('bluesales_id') is-invalid @enderror" id="bluesales_id" name="bluesales_id" value="{{ old('bluesales_id') }}" required>
                @error('bluesales_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="client_id" class="form-label">ID Клиента</label>
                <input type="text" class="form-control @error('client_id') is-invalid @enderror" id="client_id" name="client_id" value="{{ old('client_id', $selectedClientId ?? '') }}" {{ isset($selectedClientId) ? 'disabled' : '' }} required>
                @error('client_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            @isset($selectedClientId)
                <input type="hidden" name="client_id" value="{{ $selectedClientId }}">
            @endisset

            <div class="mb-3">
                <label for="total_amount" class="form-label">Общая сумма</label>
                <input type="number" step="0.01" class="form-control @error('total_amount') is-invalid @enderror" id="total_amount" name="total_amount" value="{{ old('total_amount') }}" required min="0">
                @error('total_amount')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Статус</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="">Выберите статус</option>
                    <option value="new" {{ old('status') == 'new' ? 'selected' : '' }}>Новый</option>
                    <option value="reserve" {{ old('status') == 'reserve' ? 'selected' : '' }}>Резерв</option>
                    <option value="preorder" {{ old('status') == 'preorder' ? 'selected' : '' }}>Предзаказ</option>
                    <option value="shipped" {{ old('status') == 'shipped' ? 'selected' : '' }}>Отправлен</option>
                    <option value="delivered" {{ old('status') == 'delivered' ? 'selected' : '' }}>Доставлен</option>
                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Отменен</option>
                </select>
                @error('status')
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
                        <option value="{{ $user->id }}" {{ (old('user_id') == $user->id) ? 'selected' : '' }}>
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
                <label for="internal_number" class="form-label">Внутренний номер</label>
                <input type="text" class="form-control @error('internal_number') is-invalid @enderror" id="internal_number" name="internal_number" value="{{ old('internal_number') }}">
                @error('internal_number')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="customer_comments" class="form-label">Комментарии клиента</label>
                <textarea class="form-control @error('customer_comments') is-invalid @enderror" id="customer_comments" name="customer_comments" rows="3">{{ old('customer_comments') }}</textarea>
                @error('customer_comments')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="internal_comments" class="form-label">Внутренние комментарии</label>
                <textarea class="form-control @error('internal_comments') is-invalid @enderror" id="internal_comments" name="internal_comments" rows="3">{{ old('internal_comments') }}</textarea>
                @error('internal_comments')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Сохранить заказ</button>
        </form>
    </div>
@endsection