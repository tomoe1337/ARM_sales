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
            <div class="mb-3">
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#orderFilters" aria-expanded="{{ request('created_at_from') || request('created_at_to') ? 'true' : 'false' }}" aria-controls="orderFilters">
                    <i class="fas fa-filter"></i>
                    <span>Фильтры</span>
                    <i class="fas fa-chevron-down ms-auto" style="transition: transform 0.2s;"></i>
                </button>
            </div>
            <form method="GET" action="{{ route('orders.index') }}" class="mb-4">
                <div class="collapse {{ request('created_at_from') || request('created_at_to') ? 'show' : '' }}" id="orderFilters">
                    <div class="border rounded p-3 bg-light" style="background-color: #f8f9fa !important;">
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.875rem; color: #495057;">
                                Фильтр по дате создания заказа
                            </label>
                        </div>
                        <div class="d-flex flex-wrap gap-3 align-items-end">
                            <div style="flex: 1; min-width: 180px; max-width: 220px;">
                                <label for="created_at_from" class="form-label small text-muted mb-1 fw-normal">От</label>
                                <input type="date" class="form-control form-control-sm" id="created_at_from" name="created_at_from" value="{{ request('created_at_from') }}" style="border: 1px solid #dee2e6;">
                            </div>
                            <div style="flex: 1; min-width: 180px; max-width: 220px;">
                                <label for="created_at_to" class="form-label small text-muted mb-1 fw-normal">До</label>
                                <input type="date" class="form-control form-control-sm" id="created_at_to" name="created_at_to" value="{{ request('created_at_to') }}" style="border: 1px solid #dee2e6;">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary px-3" style="font-weight: 500;">
                                    Применить
                                </button>
                                @if(request('created_at_from') || request('created_at_to'))
                                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary px-3" style="font-weight: 500;">
                                        Сбросить
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
                                <td>{{ $order->client?->name ?? 'Не указан' }}</td>
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
            @if($orders->hasPages())
                <div class="mt-3">
                    {{ $orders->links('vendor.pagination.bootstrap-5-clean') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('[data-bs-toggle="collapse"][data-bs-target^="#"]');
        filterButtons.forEach(button => {
            const targetId = button.getAttribute('data-bs-target');
            const target = document.querySelector(targetId);
            const chevron = button.querySelector('.fa-chevron-down');
            
            if (target) {
                target.addEventListener('show.bs.collapse', function() {
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                });
                target.addEventListener('hide.bs.collapse', function() {
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                });
                
                // Устанавливаем начальное состояние
                if (target.classList.contains('show') && chevron) {
                    chevron.style.transform = 'rotate(180deg)';
                }
            }
        });
    });
</script>
@endpush