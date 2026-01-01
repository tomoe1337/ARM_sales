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
            min-height: 60px;
            box-sizing: border-box;
            transition: all 0.3s ease-in-out;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            clip-path: polygon(5% 0%, 95% 0%, 90% 100%, 10% 100%);
            padding: 8px 15px;
            /* делаем трапецию */
        }

        /* Цвета по статусам - динамические */
        .funnel-step:nth-child(1) {
            background-color: #6c757d; /* Первый уровень - серый */
        }
        .funnel-step:nth-child(2) {
            background-color: #0dcaf0; /* Синий */
        }
        .funnel-step:nth-child(3) {
            background-color: #198754; /* Зеленый */
        }
        .funnel-step:nth-child(4) {
            background-color: #ffc107; /* Желтый */
        }
        .funnel-step:nth-child(5) {
            background-color: #dc3545; /* Красный */
        }
        .funnel-step:nth-child(6) {
            background-color: #6610f2; /* Фиолетовый */
        }
        .funnel-step:nth-child(7) {
            background-color: #fd7e14; /* Оранжевый */
        }
        .funnel-step:nth-child(8) {
            background-color: #20c997; /* Бирюзовый */
        }
        .funnel-step:nth-child(n+9) {
            background-color: #adb5bd; /* Для остальных - серый */
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
            font-size: 1.05rem;
            margin-bottom: 5px;
            line-height: 1.3;
            word-break: break-word;
        }

        .funnel-step .badge {
            display: inline-block;
            background-color: rgba(0, 0, 0, 0.4);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.4em 0.8em;
            min-width: 50px;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .funnel-step {
                clip-path: polygon(8% 0%, 92% 0%, 90% 100%, 10% 100%);
            }
            
            .funnel {
                padding-bottom: 20px;
            }
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

                <div class="funnel mt-3" style="height: {{ max(360, count($funnel) * 80) }}px">
                    @php
                        // $funnel уже приходит из контроллера как коллекция с динамическими статусами
                        $funnelArray = $funnel->toArray();
                        $maxCount = max(array_column($funnelArray, 'count')) ?: 1;
                        $stepHeight = 80; // высота одного шага в пикселях
                        $minWidth = 45; // минимальная ширина в процентах
                        $maxWidth = 95; // максимальная ширина в процентах
                    @endphp

                    @foreach ($funnelArray as $index => $step)
                        @php
                            // Вычисляем ширину: от minWidth до maxWidth в зависимости от значения
                            $ratio = $step['count'] / $maxCount;
                            $calculatedWidth = $minWidth + ($ratio * ($maxWidth - $minWidth));
                        @endphp
                        <div class="funnel-step" 
                             style="width: {{ $calculatedWidth }}%; top: {{ $index * $stepHeight }}px">
                            <div class="funnel-label">
                                <span class="stage-name">{{ ucfirst($step['name']) }}</span>
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
                            <th>Всего лидов</th>
                            <th>Заказов</th>
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
