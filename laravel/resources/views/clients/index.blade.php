@extends('layouts.app')

@section('title', 'Клиенты')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Клиенты</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('clients.create') }}" class="btn btn-primary">Добавить клиента</a>
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
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#clientFilters" aria-expanded="{{ request('first_contact_date_from') || request('first_contact_date_to') ? 'true' : 'false' }}" aria-controls="clientFilters">
                    <i class="fas fa-filter"></i>
                    <span>Фильтры</span>
                    <i class="fas fa-chevron-down ms-auto" style="transition: transform 0.2s;"></i>
                </button>
            </div>
            <form method="GET" action="{{ route('clients.index') }}" class="mb-4">
                <div class="collapse {{ request('first_contact_date_from') || request('first_contact_date_to') ? 'show' : '' }}" id="clientFilters">
                    <div class="border rounded p-3 bg-light" style="background-color: #f8f9fa !important;">
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.875rem; color: #495057;">
                                Фильтр по дате первого контакта
                            </label>
                        </div>
                        <div class="d-flex flex-wrap gap-3 align-items-end">
                            <div style="flex: 1; min-width: 180px; max-width: 220px;">
                                <label for="first_contact_date_from" class="form-label small text-muted mb-1 fw-normal">От</label>
                                <input type="date" class="form-control form-control-sm" id="first_contact_date_from" name="first_contact_date_from" value="{{ request('first_contact_date_from') }}" style="border: 1px solid #dee2e6;">
                            </div>
                            <div style="flex: 1; min-width: 180px; max-width: 220px;">
                                <label for="first_contact_date_to" class="form-label small text-muted mb-1 fw-normal">До</label>
                                <input type="date" class="form-control form-control-sm" id="first_contact_date_to" name="first_contact_date_to" value="{{ request('first_contact_date_to') }}" style="border: 1px solid #dee2e6;">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary px-3" style="font-weight: 500;">
                                    Применить
                                </button>
                                @if(request('first_contact_date_from') || request('first_contact_date_to'))
                                    <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary px-3" style="font-weight: 500;">
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
                            <th>Имя</th>
                            <th>Город</th>
                            <th>Статус CRM</th>
                            <th>Посл. контакт</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <td>
                                    <div><strong>{{ $client->name }}</strong></div>
                                    @if($client->full_name && $client->full_name !== $client->name)
                                        <small class="text-muted">{{ $client->full_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($client->city || $client->country)
                                        {{ $client->city }}@if($client->city && $client->country), @endif{{ $client->country }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($client->crm_status)
                                        <span class="badge bg-secondary">{{ $client->crm_status }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($client->last_contact_date)
                                        {{ $client->last_contact_date->format('d.m.Y') }}
                                        @if($client->last_contact_date->diffInDays() > 30)
                                            <br><small class="text-danger">Давно</small>
                                        @elseif($client->last_contact_date->diffInDays() > 7)
                                            <br><small class="text-warning">Недавно</small>
                                        @else
                                            <br><small class="text-success">Недавно</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-info">Просмотр</a>
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-warning">Редактировать</a>
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline">
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
            @if($clients->hasPages())
                <div class="mt-3">
                    {{ $clients->links('vendor.pagination.bootstrap-5-clean') }}
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