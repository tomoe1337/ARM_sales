@extends('layouts.app')

@section('title', 'Управление планами')

@section('content')
<div class="container py-4">
    <h1>Управление планами</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Список менеджеров и управление планами -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">Список менеджеров</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Имя менеджера</th>
                            <th>План на месяц (₽)</th>
                            <th>План на сегодня (₽)</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($managers as $manager)
                            <tr>
                                <td>{{ $manager->full_name }}</td>
                                <form action="{{ route('plans.update', $manager) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td>
                                        <input name="monthly_plan" type="number" class="form-control" 
                                               value="{{ $plans[$manager->id]->monthly_plan ?? 0 }}" step="0.01">
                                    </td>
                                    <td>
                                        <input name="daily_plan" type="number" class="form-control" 
                                               value="{{ $plans[$manager->id]->daily_plan ?? 0 }}" step="0.01">
                                    </td>
                                    <td>
                                        <button type="submit" class="btn btn-success">Сохранить</button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Общий план отдела -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Общий план отдела</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">План на месяц</h5>
                            <p class="card-text">{{ number_format($totalMonthlyPlan, 2) }} ₽</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">План на сегодня</h5>
                            <p class="card-text">{{ number_format($totalDailyPlan, 2) }} ₽</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 