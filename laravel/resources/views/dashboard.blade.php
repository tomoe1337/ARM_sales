@extends('layouts.app')

@section('title', 'Панель управления')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-message" style="transition: all 0.5s ease-out;">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-message" style="transition: all 0.5s ease-out;">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Панель управления</h1>
                @if(auth()->user()->isWorking())
                    <form action="{{ route('work-sessions.end') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-stop-circle"></i> Закончить смену
                        </button>
                    </form>
                @else
                    <form action="{{ route('work-sessions.start') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-play-circle"></i> Начать смену
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if(!auth()->user()->isWorking())
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="alert alert-info">
                            <h4 class="alert-heading">Внимание!</h4>
                            <p>Для начала работы необходимо начать смену, нажав кнопку "Начать смену" в верхней части страницы.</p>
                            <p>После начала смены вы получите доступ ко всем инструментам и функциям системы.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Статистика -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Выручка за месяц</h5>
                        <p class="card-text text-center display-6">{{ number_format($dashboardData['monthlyRevenue'], 2) }}&nbsp;₽</p>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $dashboardData['percentageCompleted'] }}%;"
                                 aria-valuenow="{{ $dashboardData['percentageCompleted'] }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                {{ round($dashboardData['percentageCompleted'], 2) }}%
                            </div>
                        </div>
                        <p class="mt-2 text-center mb-0" style="white-space: nowrap;">{{ number_format($dashboardData['monthlyRevenue'], 2) }}&nbsp;₽ из&nbsp;{{ number_format($dashboardData['monthlyPlan'], 2) }}&nbsp;₽</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Оплаченные заказы</h5>
                        <h2 class="card-text">{{ $dashboardData['wonDealsCount'] }}</h2>
                        <a href="{{ route('orders.index') }}" class="text-white">Подробнее <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Выручка за сегодня</h5>
                        <h2 class="card-text">{{ number_format($dashboardData['todayRevenue'], 2) }}&nbsp;₽</h2>
                        <a href="{{ route('orders.index') }}" class="text-white">Подробнее <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Активные задачи</h5>
                        <h2 class="card-text">{{ $dashboardData['activeTasksCount'] }}</h2>
                        <a href="{{ route('tasks.index') }}" class="text-white">Подробнее <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->isHead())
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Сотрудники</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Имя</th>
                                            <th>Статус</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dashboardData['employees'] as $employee)
                                            <tr>
                                                <td>{{ "$employee->full_name $employee->name" }}</td>
                                                <td>
                                                    @if($employee->isWorking())
                                                        <span class="badge bg-success">Работает</span>
                                                    @else
                                                        <span class="badge bg-secondary">Не работает</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('work-sessions.report', $employee->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-chart-bar"></i> Отчет
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Последние сделки и клиенты -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Последние заказы</h5>
                        <a href="{{ route('orders.index') }}" class="btn btn-primary btn-sm">Все заказы</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID BlueSales</th>
                                        <th>Клиент</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['latestOrders'] as $order)
                                        <tr>
                                            <td><a href="{{ route('orders.show', $order) }}">{{ $order->bluesales_id }}</a></td>
                                            <td>{{ $order->client?->name ?? 'Не указан' }}</td>
                                            <td>{{ number_format($order->total_amount, 2) }} ₽</td>
                                            <td>
                                                @switch($order->status)
                                                    @case('new')
                                                        <span class="badge bg-secondary">Новый</span>
                                                        @break
                                                    @case('delivered')
                                                        <span class="badge bg-success">Доставлен</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">Отменен</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-warning">В работе</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Последние клиенты</h5>
                        <a href="{{ route('clients.index') }}" class="btn btn-primary btn-sm">Все клиенты</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Имя</th>
                                        <th>Заказов</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['latestClients'] as $client)
                                        <tr>
                                            <td><a href="{{ route('clients.show', $client) }}">{{ $client->name }}</a></td>
                                            <td>{{ $client->orders_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Активные задачи -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Активные задачи</h5>
                        <a href="{{ route('tasks.index') }}" class="btn btn-primary btn-sm">Все задачи</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Задача</th>
                                        <th>Исполнитель</th>
                                        <th>Срок</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['activeTasks'] as $task)
                                        <tr>
                                            <td><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td>
                                            <td>{{ $task->assignee?->full_name ?? 'Не назначен' }}</td>
                                            <td>{{ $task->deadline->format('d.m.Y') }}</td>
                                            <td>
                                                @switch($task->status)
                                                    @case('pending')
                                                        <span class="badge bg-secondary">Новая</span>
                                                        @break
                                                    @case('in_progress')
                                                        <span class="badge bg-primary">В работе</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">Выполнена</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">Отменена</span>
                                                        @break
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');

        function fadeOut(element) {
            if (element) {
                // Сохраняем текущую высоту
                const height = element.offsetHeight;
                element.style.height = height + 'px';

                // Сначала делаем прозрачным
                element.style.opacity = '0';

                // Затем плавно уменьшаем высоту
                setTimeout(() => {
                    element.style.height = '0';
                    element.style.margin = '0';
                    element.style.padding = '0';
                    element.style.overflow = 'hidden';
                }, 500);

                // Удаляем элемент после завершения анимации
                setTimeout(() => {
                    element.remove();
                }, 1000);
            }
        }

        if (successMessage) {
            setTimeout(() => {
                fadeOut(successMessage);
            }, 2000);
        }

        if (errorMessage) {
            setTimeout(() => {
                fadeOut(errorMessage);
            }, 2000);
        }
    });
</script>
@endpush
