@extends('layouts.app')

@section('title', 'Анализ продаж')

@section('content')
    <div class="container">

        <!-- Заголовок -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Анализ продаж за неделю</h2>
            @if(!empty($weeklyReport))
                <span class="text-muted">С {{ $weeklyReport['week_start'] }} по {{ $weeklyReport['week_end'] }}</span>
            @else
                <span class="text-muted">Нет данных за текущую неделю</span>
            @endif
        </div>

        <!-- Блок KPI или сообщение о отсутствии данных -->
        @if(!empty($weeklyReport))

            <!-- Кнопка перехода к детальному анализу -->
            <div class="mb-4 text-end">
                <a href="{{ route('analyticsAi.generate') }}" class="btn btn-primary">
                    Посмотреть детальный анализ
                </a>
            </div>

            <!-- Блок KPI -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card border-start border-primary border-4 h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Всего сделок</h6>
                            <h3 class="fw-bold">{{ number_format($weeklyReport['total_deals'] ?? 0, 0, '', ' ') }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-start border-success border-4 h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Успешных сделок</h6>
                            <h3 class="fw-bold">{{ number_format($weeklyReport['successful_deals'] ?? 0, 0, '', ' ') }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-start border-info border-4 h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Конверсия</h6>
                            <h3 class="fw-bold">{{ $weeklyReport['conversion_rate'] ?? '—' }}%</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-start border-warning border-4 h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Выручка</h6>
                            <h3 class="fw-bold">{{ number_format($weeklyReport['revenue'] ?? 0, 0, '', ' ') }} ₽</h3>
                        </div>
                    </div>
                </div>
            </div>

        @else

            <!-- Сообщение: нет данных -->
            <div class="alert alert-info mb-5">
                За текущую неделю данные ещё не собраны. Вы можете создать отчет вручную.
            </div>

        @endif

        <!-- История отчетов -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">История недельных отчетов</h5>
                @if(!empty($previousReports) && count($previousReports) > 0)
                    <span class="badge bg-secondary">{{ count($previousReports) }} отчетов</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if(!empty($previousReports) && count($previousReports) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Период</th>
                                <th>Сделки</th>
                                <th>Выручка</th>
                                <th>Конверсия</th>
                                <th class="text-end">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($previousReports as $report)
                                <tr>
                                    <td>{{ $report['week_start'] }} – {{ $report['week_end'] }}</td>
                                    <td>{{ number_format($report['successful_deals'], 0, '', ' ') }} / {{ number_format($report['total_deals'], 0, '', ' ') }}</td>
                                    <td>{{ number_format($report['revenue'], 0, '', ' ') }} ₽</td>
                                    <td>{{ $report['conversion_rate'] }}%</td>
                                    <td class="text-end">
                                        <a href="{{ route('analyticsAi.report', ['analysisAiReport' => $report['id']]) }}" class="btn btn-sm btn-outline-primary">Просмотр</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">Нет истории отчетов</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
