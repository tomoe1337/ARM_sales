@extends('layouts.app')

@section('title', 'Синхронизация с BlueSales')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Синхронизация с BlueSales CRM</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        
                        @if(session('sync_result'))
                            @php $result = session('sync_result'); @endphp
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5>Результаты синхронизации</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Период:</strong> {{ $result['period']['start_date'] }} - {{ $result['period']['end_date'] }}</p>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Клиенты:</h6>
                                            <ul class="list-unstyled">
                                                <li><span class="badge bg-success">Создано: {{ $result['customers']['created'] }}</span></li>
                                                <li><span class="badge bg-info">Обновлено: {{ $result['customers']['updated'] }}</span></li>
                                                @if(isset($result['customers']['unchanged']))
                                                    <li><span class="badge bg-secondary">Не изменено: {{ $result['customers']['unchanged'] }}</span></li>
                                                @endif
                                                <li><span class="badge bg-danger">Ошибок: {{ $result['customers']['errors'] }}</span></li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Заказы:</h6>
                                            <ul class="list-unstyled">
                                                <li><span class="badge bg-success">Создано: {{ $result['orders']['created'] }}</span></li>
                                                <li><span class="badge bg-info">Обновлено: {{ $result['orders']['updated'] }}</span></li>
                                                @if(isset($result['orders']['unchanged']))
                                                    <li><span class="badge bg-secondary">Не изменено: {{ $result['orders']['unchanged'] }}</span></li>
                                                @endif
                                                <li><span class="badge bg-warning">Пропущено: {{ $result['orders']['skipped'] }}</span></li>
                                                <li><span class="badge bg-danger">Ошибок: {{ $result['orders']['errors'] }}</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('bluesales.sync') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="login" class="form-label">Логин BlueSales <span class="text-danger">*</span></label>
                            <input 
                                type="email" 
                                class="form-control @error('login') is-invalid @enderror" 
                                id="login" 
                                name="login" 
                                value="{{ old('login') }}" 
                                placeholder="Введите ваш email (логин) BlueSales"
                                required
                            >
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_key" class="form-label">API ключ BlueSales <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('api_key') is-invalid @enderror" 
                                id="api_key" 
                                name="api_key" 
                                value="{{ old('api_key') }}" 
                                placeholder="Введите ваш API ключ BlueSales"
                                required
                            >
                            @error('api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                API ключ можно получить в настройках вашего аккаунта BlueSales
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="days_back" class="form-label">Период синхронизации (дней назад) <span class="text-danger">*</span></label>
                            <select class="form-select @error('days_back') is-invalid @enderror" id="days_back" name="days_back" required>
                                <option value="7" {{ old('days_back', 30) == 7 ? 'selected' : '' }}>7 дней</option>
                                <option value="30" {{ old('days_back', 30) == 30 ? 'selected' : '' }}>30 дней (рекомендуется)</option>
                                <option value="90" {{ old('days_back', 30) == 90 ? 'selected' : '' }}>90 дней</option>
                                <option value="365" {{ old('days_back', 30) == 365 ? 'selected' : '' }}>1 год</option>
                            </select>
                            @error('days_back')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Будут загружены данные за указанный период
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Информация о синхронизации:</h6>
                            <ul class="mb-0">
                                <li>Используйте ваш email (логин) и API ключ из BlueSales</li>
                                <li>Новые клиенты из BlueSales будут добавлены в систему</li>
                                <li>Существующие клиенты будут обновлены</li>
                                <li>Заказы будут привязаны к соответствующим клиентам</li>
                                <li>Процесс может занять несколько минут в зависимости от объема данных</li>
                            </ul>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sync-alt"></i> Начать синхронизацию
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection