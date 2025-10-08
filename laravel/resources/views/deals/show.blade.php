@extends('layouts.app')

@section('title', 'Информация о клиенте')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Информация о сделке № {{ $deal->id }}: {{ $deal->title }}</div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Название:</dt>
                            <dd class="col-sm-9">{{ $deal->title ?? 'Не указано' }}</dd>
                            <dt class="col-sm-3">Статус:</dt>
                            <dd class="col-sm-9">
                                @switch($deal->status)
                                    @case('new')
                                        <span class="badge bg-secondary">Новая</span>
                                        @break
                                    @case('in_progress')
                                        <span class="badge bg-primary">В работе</span>
                                        @break
                                    @case('won')
                                        <span class="badge bg-success">Выполнена</span>
                                        @break
                                    @case('lost')
                                        <span class="badge bg-success">Проиграна</span>
                                        @break
                                @endswitch
                            </dd>

                            <dt class="col-sm-3">Сумма:</dt>
                            <dd class="col-sm-9">{{ $deal->amount . " р."?? 'Не указано' }}</dd>

                            <dt class="col-sm-3">Создана:</dt>
                            <dd class="col-sm-9">{{ $deal->created_at ?? 'Не указано' }}</dd>

                            <dt class="col-sm-3">Закрыта:</dt>
                            <dd class="col-sm-9">{{ $deal->closed_at ?? 'Не указано' }}</dd>

                            <dt class="col-sm-3">Сотрудник:</dt>
                            <dd class="col-sm-9">{{ $deal->user->full_name ?? 'Не указано' }}</dd>

                            <dt class="col-sm-3">Клиент:</dt>
                            <dd class="col-sm-9">{{ $deal->client->name ?? 'Не указано' }}</dd>
                        </dl>
                        <div class="col-md-6">
                            <h5 class="card-title">Описание</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $deal->description ?: 'Описание отсутствует' }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('deals.index') }}" class="btn btn-secondary">
                                Назад к списку сделок
                            </a>
                            <a href="{{ route('deals.edit', $deal) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
