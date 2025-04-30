@extends('layouts.app')

@section('title', 'Сделки')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Сделки</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('deals.create') }}" class="btn btn-primary">Добавить сделку</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Дата закрытия</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deals as $deal)
                            <tr>
                                <td>{{ $deal->title }}</td>
                                <td>{{ $deal->client->name }}</td>
                                <td>{{ number_format($deal->amount, 2) }} ₽</td>
                                <td>
                                    @switch($deal->status)
                                        @case('new')
                                            <span class="badge bg-secondary">Новая</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-primary">В работе</span>
                                            @break
                                        @case('won')
                                            <span class="badge bg-success">Выиграна</span>
                                            @break
                                        @case('lost')
                                            <span class="badge bg-danger">Проиграна</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $deal->closed_at ? $deal->closed_at->format('d.m.Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('deals.show', $deal) }}" class="btn btn-sm btn-info">Просмотр</a>
                                    <a href="{{ route('deals.edit', $deal) }}" class="btn btn-sm btn-warning">Редактировать</a>
                                    <form action="{{ route('deals.destroy', $deal) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Вы уверены?')">Удалить</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 