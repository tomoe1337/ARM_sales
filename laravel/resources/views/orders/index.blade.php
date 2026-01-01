@extends('layouts.app')

@section('title', 'Заказы')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Заказы</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('orders.create') }}" class="btn btn-primary">Добавить заказ</a>
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
                            <th>ID BlueSales</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $order->bluesales_id }}</td>
                                <td>{{ $order->client->name }}</td>
                                <td>{{ number_format($order->total_amount, 2) }} ₽</td>
                                <td>
                                    @switch($order->status)
                                        @case('new')
                                            <span class="badge bg-secondary">Новый</span>
                                            @break
                                        @case('reserve')
                                            <span class="badge bg-warning">Резерв</span>
                                            @break
                                        @case('preorder')
                                            <span class="badge bg-info">Предзаказ</span>
                                            @break
                                        @case('shipped')
                                            <span class="badge bg-primary">Отправлен</span>
                                            @break
                                        @case('delivered')
                                            <span class="badge bg-success">Доставлен</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Отменен</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $order->created_at ? $order->created_at->format('d.m.Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-info">Просмотр</a>
                                    <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-warning">Редактировать</a>
                                    <form action="{{ route('orders.destroy', $order) }}" method="POST" class="d-inline">
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