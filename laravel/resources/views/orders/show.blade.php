@extends('layouts.app')

@section('title', 'Информация о заказе')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Информация о заказе № {{ $order->id }}: {{ $order->bluesales_id }}</div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">ID BlueSales:</dt>
                            <dd class="col-sm-9">{{ $order->bluesales_id ?? 'Не указано' }}</dd>
                            
                            <dt class="col-sm-3">Статус:</dt>
                            <dd class="col-sm-9">
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
                            </dd>

                            <dt class="col-sm-3">Общая сумма:</dt>
                            <dd class="col-sm-9">{{ number_format($order->total_amount, 2) }} ₽</dd>

                            <dt class="col-sm-3">Скидка:</dt>
                            <dd class="col-sm-9">{{ ($order->discount * 100) }}%</dd>

                            <dt class="col-sm-3">Предоплата:</dt>
                            <dd class="col-sm-9">{{ number_format($order->prepay, 2) }} ₽</dd>

                            <dt class="col-sm-3">Внутренний номер:</dt>
                            <dd class="col-sm-9">{{ $order->internal_number ?? 'Не указано' }}</dd>

                            <dt class="col-sm-3">Создан:</dt>
                            <dd class="col-sm-9">{{ $order->created_at ? $order->created_at->format('d.m.Y H:i') : 'Не указано' }}</dd>

                            <dt class="col-sm-3">Обновлен:</dt>
                            <dd class="col-sm-9">{{ $order->updated_at ? $order->updated_at->format('d.m.Y H:i') : 'Не указано' }}</dd>

                            <dt class="col-sm-3">Сотрудник:</dt>
                            <dd class="col-sm-9">{{ $order->user->full_name ?? 'Не указано' }}</dd>

                            <dt class="col-sm-3">Клиент:</dt>
                            <dd class="col-sm-9">{{ $order->client->name ?? 'Не указано' }}</dd>

                            @if($order->deal_id)
                                <dt class="col-sm-3">Связанная сделка:</dt>
                                <dd class="col-sm-9">
                                    <a href="{{ route('deals.show', $order->deal_id) }}">
                                        Сделка #{{ $order->deal_id }}
                                    </a>
                                </dd>
                            @endif
                        </dl>

                        @if($order->customer_comments)
                            <div class="col-md-12 mt-3">
                                <h5 class="card-title">Комментарии клиента</h5>
                                <div class="card">
                                    <div class="card-body">
                                        {{ $order->customer_comments }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($order->internal_comments)
                            <div class="col-md-12 mt-3">
                                <h5 class="card-title">Внутренние комментарии</h5>
                                <div class="card">
                                    <div class="card-body">
                                        {{ $order->internal_comments }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                                Назад к списку заказов
                            </a>
                            <a href="{{ route('orders.edit', $order) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection