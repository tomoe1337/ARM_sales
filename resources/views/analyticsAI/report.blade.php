@extends('layouts.app')

@section('title', 'Детальный анализ продаж')

@section('content')
    <style>
        .funnel {
            position: relative;
            height: 360px;
            padding-top: 20px;
        }

        .funnel-step {
            color: white;
            font-weight: bold;
            text-align: center;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            min-height: 50px;
            box-sizing: border-box;
            transition: all 0.3s ease-in-out;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            clip-path: polygon(5% 0%, 95% 0%, 90% 100%, 10% 100%);
            /* делаем трапецию */
        }

        /* Цвета по статусам */
        .funnel-step:nth-child(1) {
            background-color: #6c757d; /* Все лиды */
            top: 0;
            clip-path: polygon(5% 0%, 95% 0%, 90% 100%, 10% 100%);
        }
        .funnel-step:nth-child(2) {
            background-color: #0dcaf0; /* В работе */
            top: 80px;
        }
        .funnel-step:nth-child(3) {
            background-color: #198754; /* Выиграно */
            top: 160px;
        }
        .funnel-step:nth-child(4) {
            background-color: #dc3545; /* Проиграно */
            top: 240px;
        }

        .funnel-step .funnel-label {
            display: block;
            width: 100%;
            text-align: center;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .funnel-step .stage-name {
            display: block;
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .funnel-step .badge {
            display: inline-block;
            background-color: rgba(0, 0, 0, 0.3);
            color: white;
            font-size: 0.9rem;
            padding: 0.3em 0.6em;
            min-width: 40px;
        }

        @media (max-width: 768px) {
            .funnel-step {
                clip-path: polygon(8% 0%, 92% 0%, 90% 100%, 10% 100%);
            }

            .funnel-step:nth-child(1) { top: 0; }
            .funnel-step:nth-child(2) { top: 90px; }
            .funnel-step:nth-child(3) { top: 180px; }
            .funnel-step:nth-child(4) { top: 270px; }
        }
    </style>

    <div class="container py-2">

        <!-- Заголовок -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Детальный анализ продаж</h1>
            <a href="{{ route('analyticsAi.index') }}" class="btn btn-outline-secondary">
                Назад к сводке
            </a>
        </div>

        <!-- Воронка + таблица сотрудников -->
        <div class="row mt-5 align-items-start">
            <!-- Воронка продаж -->
            <div class="col-md-6">
                <h4>📊 Воронка продаж за неделю</h4>

                <div class="funnel mt-3">
                    @php
                        $funnel = [
                            ['name' => 'Все лиды', 'count' => $funnel['all_leads'] ?? 0],
                            ['name' => 'В работе', 'count' => $funnel['in_progress'] ?? 0],
                            ['name' => 'Выиграно', 'count' => $funnel['won'] ?? 0],
                            ['name' => 'Проиграно', 'count' => $funnel['lost'] ?? 0],
                        ];

                        $maxCount = max(array_column($funnel, 'count')) ?: 1;
                    @endphp

                    @foreach ($funnel as $index => $step)
                        <div class="funnel-step" style="width: {{ ($step['count'] / $maxCount) * 100 }}%">
                            <div class="funnel-label">
                                <span class="stage-name">{{ $step['name'] }}</span>
                                <span class="badge bg-dark">{{ $step['count'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Таблица по сотрудникам -->
            <div class="col-md-6">
                <h4>👥 Эффективность сотрудников</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Сотрудник</th>
                            <th>Всего сделок</th>
                            <th>Выигранные</th>
                            <th>Конверсия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($employeeStats as $stat)
                            <tr>
                                <td>{{ $stat['name'] }}</td>
                                <td>{{ $stat['total'] }}</td>
                                <td>{{ $stat['won'] }}</td>
                                <td>{{ $stat['conversion_rate'] }}%</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Общий анализ -->
        <div class="mt-3">
            <div class="row g-4">
                <!-- Что сделано хорошо -->
                <div class="col-md-6">
                    <div class="card border-success border-3 h-100 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">✅ Что сделано хорошо</h5>
                        </div>
                        <div class="card-body">
                            @if(!empty($analysis['done_well']))
                                <p class="card-text">{{ $analysis['done_well'] }}</p>
                            @else
                                <p class="text-muted">Нет данных.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Что можно улучшить -->
                <div class="col-md-6">
                    <div class="card border-danger border-3 h-100 shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">⚠️ Что можно улучшить</h5>
                        </div>
                        <div class="card-body">
                            @if(!empty($analysis['done_bad']))
                                <p class="card-text">{{ $analysis['done_bad'] }}</p>
                            @else
                                <p class="text-muted">Претензий нет — отличная работа!</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!empty($analysis['general_result']))
            <div class="alert alert-info mt-4">
                <h5>Общий результат недели:</h5>
                <p class="mb-0">{{ $analysis['general_result'] }}</p>
            </div>
        @endif
    </div>
@endsection
