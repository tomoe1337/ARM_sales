@extends('layouts.app')

@section('title', 'Детальный анализ продаж')

@section('content')
<div class="container py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Детальный анализ продаж</h1>
            <p class="text-muted mb-0">
                {{ $analysisAiReport->start_date->format('d.m.Y') }} – 
                {{ $analysisAiReport->end_date->format('d.m.Y') }}
            </p>
        </div>
        <a href="{{ route('analyticsAi.index') }}" class="btn btn-outline-secondary">
            Назад к сводке
        </a>
    </div>

    <!-- Сравнение с прошлой неделей -->
    @if($comparison)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📊 Сравнение с прошлой неделей</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="mb-2">
                                <small class="text-muted">Лиды</small>
                                <div class="h5 mb-0">
                                    {{ $comparison['leads'] > 0 ? '+' : '' }}{{ $comparison['leads'] }}%
                                    @if($comparison['leads'] > 0)
                                        <span class="text-success">↑</span>
                                    @elseif($comparison['leads'] < 0)
                                        <span class="text-danger">↓</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <small class="text-muted">Заказы</small>
                                <div class="h5 mb-0">
                                    {{ $comparison['orders'] > 0 ? '+' : '' }}{{ $comparison['orders'] }}%
                                    @if($comparison['orders'] > 0)
                                        <span class="text-success">↑</span>
                                    @elseif($comparison['orders'] < 0)
                                        <span class="text-danger">↓</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <small class="text-muted">Выручка</small>
                                <div class="h5 mb-0">
                                    {{ $comparison['revenue'] > 0 ? '+' : '' }}{{ $comparison['revenue'] }}%
                                    @if($comparison['revenue'] > 0)
                                        <span class="text-success">↑</span>
                                    @elseif($comparison['revenue'] < 0)
                                        <span class="text-danger">↓</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <small class="text-muted">Конверсия</small>
                                <div class="h5 mb-0">
                                    {{ $comparison['conversion'] > 0 ? '+' : '' }}{{ $comparison['conversion'] }}%
                                    @if($comparison['conversion'] > 0)
                                        <span class="text-success">↑</span>
                                    @elseif($comparison['conversion'] < 0)
                                        <span class="text-danger">↓</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Воронка с конверсией -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📊 Воронка продаж</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="funnelView" id="funnelShort" checked onchange="toggleFunnelView(false)">
                        <label class="btn btn-outline-primary" for="funnelShort">Краткая</label>
                        
                        <input type="radio" class="btn-check" name="funnelView" id="funnelFull" onchange="toggleFunnelView(true)">
                        <label class="btn btn-outline-primary" for="funnelFull">Полная</label>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $maxCount = $funnel[0]['count'] ?? 1;
                    @endphp
                    
                    <!-- Краткая версия -->
                    <div id="funnelShortView">
                        @foreach($funnel as $step)
                            @php
                                $widthPercent = $maxCount > 0 ? ($step['count'] / $maxCount) * 100 : 0;
                                $widthPercent = min(100, max(10, $widthPercent));
                            @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>
                                        <strong>{{ $step['name'] }}</strong>
                                        @if($step['conversion'] !== null)
                                            <small class="text-muted"> · {{ $step['conversion'] }}%</small>
                                        @endif
                                    </div>
                                    <div class="fw-bold">{{ number_format($step['count'], 0, '', ' ') }}</div>
                                </div>
                                <div class="funnel-bar-container" style="position: relative; height: 35px;">
                                    <div class="funnel-bar" 
                                         style="
                                             width: {{ $widthPercent }}%;
                                             height: 100%;
                                             background: {{ $loop->first ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : ($loop->last ? 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' : 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)') }};
                                             margin: 0 auto;
                                             border-radius: 6px;
                                             box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                                             transition: all 0.3s ease;
                                         "
                                         onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
                                         onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';">
                                    </div>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <div class="text-center" style="height: 8px;">
                                    <svg width="16" height="8" style="margin: 0 auto; display: block;">
                                        <path d="M 8 0 L 8 8" stroke="#dee2e6" stroke-width="1.5" fill="none"/>
                                        <path d="M 4 6 L 8 8 L 12 6" stroke="#dee2e6" stroke-width="1.5" fill="none"/>
                                    </svg>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    
                    <!-- Полная версия -->
                    <div id="funnelFullView" style="display: none;">
                        @php
                            $maxCountFull = $fullFunnel[0]['count'] ?? 1;
                        @endphp
                        @foreach($fullFunnel as $step)
                            @php
                                $widthPercent = $maxCountFull > 0 ? ($step['count'] / $maxCountFull) * 100 : 0;
                                $widthPercent = min(100, max(10, $widthPercent));
                            @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>
                                        <strong>{{ $step['name'] }}</strong>
                                        @if($step['conversion'] !== null)
                                            <small class="text-muted"> · {{ $step['conversion'] }}%</small>
                                        @endif
                                    </div>
                                    <div class="fw-bold">{{ number_format($step['count'], 0, '', ' ') }}</div>
                                </div>
                                <div class="funnel-bar-container" style="position: relative; height: 35px;">
                                    <div class="funnel-bar" 
                                         style="
                                             width: {{ $widthPercent }}%;
                                             height: 100%;
                                             background: {{ $loop->first ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : ($loop->last ? 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' : 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)') }};
                                             margin: 0 auto;
                                             border-radius: 6px;
                                             box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                                             transition: all 0.3s ease;
                                         "
                                         onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
                                         onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';">
                                    </div>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <div class="text-center" style="height: 8px;">
                                    <svg width="16" height="8" style="margin: 0 auto; display: block;">
                                        <path d="M 8 0 L 8 8" stroke="#dee2e6" stroke-width="1.5" fill="none"/>
                                        <path d="M 4 6 L 8 8 L 12 6" stroke="#dee2e6" stroke-width="1.5" fill="none"/>
                                    </svg>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Сегментация по источникам -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">🎯 По источникам</h5>
                </div>
                <div class="card-body">
                    @if(count($segments) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Источник</th>
                                        <th class="text-end">Лиды</th>
                                        <th class="text-end">Конверсия</th>
                                        <th class="text-end">Выручка</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($segments as $segment)
                                    <tr>
                                        <td><strong>{{ $segment['source'] }}</strong></td>
                                        <td class="text-end">{{ $segment['leads'] }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-{{ $segment['conversion'] >= 15 ? 'success' : 'warning' }}">
                                                {{ $segment['conversion'] }}%
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format($segment['revenue'], 0, '', ' ') }} ₽</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Нет данных по источникам</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Сотрудники с выручкой -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">👥 Эффективность сотрудников</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Сотрудник</th>
                                    <th class="text-end">Лиды</th>
                                    <th class="text-end">Заказы</th>
                                    <th class="text-end">Конверсия</th>
                                    <th class="text-end">Выручка</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employeeStats as $stat)
                                <tr>
                                    <td><strong>{{ $stat['name'] }}</strong></td>
                                    <td class="text-end">{{ $stat['leads'] }}</td>
                                    <td class="text-end">{{ $stat['orders'] }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $stat['conversion'] >= 15 ? 'success' : 'warning' }}">
                                            {{ $stat['conversion'] }}%
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($stat['revenue'], 0, '', ' ') }} ₽</strong>
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

    <!-- AI Анализ -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-success border-3 h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">✅ Что сделано хорошо</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">{{ $analysisAiReport->done_well }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-danger border-3 h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">⚠️ Что можно улучшить</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">{{ $analysisAiReport->done_bad }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($analysisAiReport->general_result)
    <div class="alert alert-info mt-4">
        <h5>💡 Рекомендации:</h5>
        <p class="mb-0">{{ $analysisAiReport->general_result }}</p>
    </div>
    @endif
</div>

<script>
function toggleFunnelView(showFull) {
    const shortView = document.getElementById('funnelShortView');
    const fullView = document.getElementById('funnelFullView');
    
    if (showFull) {
        shortView.style.display = 'none';
        fullView.style.display = 'block';
    } else {
        shortView.style.display = 'block';
        fullView.style.display = 'none';
    }
}
</script>
@endsection
