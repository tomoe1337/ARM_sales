@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Выручка за месяц</h5>
                    <p class="card-text text-center display-6">{{ number_format($dashboardData['monthlyRevenue'], 2) }} ₽</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">План на месяц</h5>
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" 
                             style=\"width: {{ $dashboardData['percentageCompleted'] }}%;\" 
                             aria-valuenow=\"{{ $dashboardData['percentageCompleted'] }}\" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $dashboardData['percentageCompleted'] }}%\
                        </div>
                    </div>
                    <p class="card-text text-center">
                        {{ number_format($dashboardData['monthlyRevenue'], 2) }} ₽ из {{ number_format($dashboardData['monthlyPlan'], 2) }} ₽
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Количество клиентов</h5>
                    <p class="card-text text-center display-6">{{ $dashboardData['clientsCount'] }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($dashboardData['user']->isHead())
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Активные задачи</h5>
                </div>
                <div class="card-body">
                    @if($dashboardData['activeTasks']->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
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
                                            <td>{{ $task->title }}</td>
                                            <td>{{ $task->assignee->name }}</td>
                                            <td>{{ $task->due_date->format('d.m.Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $task->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ $task->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">Нет активных задач</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection 