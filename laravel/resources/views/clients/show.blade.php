@extends('layouts.app')

@section('title', 'Информация о клиенте')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Информация о клиенте № {{ $client->id }}: {{ $client->name }}
                    @if($client->bluesales_id)
                        <span class="badge bg-info ms-2">Синхронизирован с BlueSales</span>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Имя:</dt>
                        <dd class="col-sm-9">{{ $client->name }}</dd>

                        @if($client->full_name && $client->full_name !== $client->name)
                            <dt class="col-sm-3">Полное имя:</dt>
                            <dd class="col-sm-9">{{ $client->full_name }}</dd>
                        @endif

                        <dt class="col-sm-3">Телефон:</dt>
                        <dd class="col-sm-9">{{ $client->phone ?? 'Не указан' }}</dd>

                        <dt class="col-sm-3">Email:</dt>
                        <dd class="col-sm-9">{{ $client->email ?? 'Не указан' }}</dd>

                        @if($client->country || $client->city)
                            <dt class="col-sm-3">Местоположение:</dt>
                            <dd class="col-sm-9">
                                {{ $client->city }}@if($client->city && $client->country), @endif{{ $client->country }}
                            </dd>
                        @endif

                        @if($client->birth_date)
                            <dt class="col-sm-3">Дата рождения:</dt>
                            <dd class="col-sm-9">{{ $client->birth_date->format('d.m.Y') }} (возраст: {{ $client->birth_date->age }} лет)</dd>
                        @endif

                        @if($client->gender)
                            <dt class="col-sm-3">Пол:</dt>
                            <dd class="col-sm-9">{{ $client->gender === 'male' ? 'Мужской' : 'Женский' }}</dd>
                        @endif

                        <dt class="col-sm-3">Адрес:</dt>
                        <dd class="col-sm-9">{{ $client->address ?? 'Не указан' }}</dd>

                        @if($client->crm_status)
                            <dt class="col-sm-3">Статус в CRM:</dt>
                            <dd class="col-sm-9"><span class="badge bg-secondary">{{ $client->crm_status }}</span></dd>
                        @endif

                        @if($client->first_contact_date)
                            <dt class="col-sm-3">Дата первого контакта:</dt>
                            <dd class="col-sm-9">
                                {{ $client->first_contact_date->format('d.m.Y H:i') }}
                                <small class="text-muted">({{ $client->first_contact_date->diffForHumans() }})</small>
                            </dd>
                        @endif

                        @if($client->next_contact_date)
                            <dt class="col-sm-3">Дата следующего контакта:</dt>
                            <dd class="col-sm-9">
                                {{ $client->next_contact_date->format('d.m.Y H:i') }}
                                @if($client->next_contact_date->isPast())
                                    <span class="badge bg-danger ms-2">Просрочен</span>
                                @elseif($client->next_contact_date->isToday())
                                    <span class="badge bg-warning ms-2">Сегодня</span>
                                @elseif($client->next_contact_date->isTomorrow())
                                    <span class="badge bg-info ms-2">Завтра</span>
                                @else
                                    <small class="text-muted">({{ $client->next_contact_date->diffForHumans() }})</small>
                                @endif
                            </dd>
                        @endif

                        @if($client->last_contact_date)
                            <dt class="col-sm-3">Дата последнего контакта:</dt>
                            <dd class="col-sm-9">
                                {{ $client->last_contact_date->format('d.m.Y H:i') }}
                                <small class="text-muted">({{ $client->last_contact_date->diffForHumans() }})</small>
                            </dd>
                        @endif

                        @if($client->source)
                            <dt class="col-sm-3">Источник:</dt>
                            <dd class="col-sm-9">{{ $client->source }}</dd>
                        @endif

                        @if($client->sales_channel)
                            <dt class="col-sm-3">Канал продаж:</dt>
                            <dd class="col-sm-9">{{ $client->sales_channel }}</dd>
                        @endif

                        @if($client->vk_id || $client->ok_id)
                            <dt class="col-sm-3">Соцсети:</dt>
                            <dd class="col-sm-9">
                                @if($client->vk_id)
                                    <span class="badge bg-primary me-1">VK: {{ $client->vk_id }}</span>
                                @endif
                                @if($client->ok_id)
                                    <span class="badge bg-warning">OK: {{ $client->ok_id }}</span>
                                @endif
                            </dd>
                        @endif

                        @if($client->tags)
                            <dt class="col-sm-3">Теги:</dt>
                            <dd class="col-sm-9">
                                @foreach(explode(',', $client->tags) as $tag)
                                    <span class="badge bg-light text-dark me-1">{{ trim($tag) }}</span>
                                @endforeach
                            </dd>
                        @endif

                        @if($client->additional_contacts)
                            <dt class="col-sm-3">Доп. контакты:</dt>
                            <dd class="col-sm-9">{{ $client->additional_contacts }}</dd>
                        @endif

                        @if($client->notes)
                            <dt class="col-sm-3">Заметки:</dt>
                            <dd class="col-sm-9">{{ $client->notes }}</dd>
                        @endif

                        <dt class="col-sm-3">Описание:</dt>
                        <dd class="col-sm-9">{{ $client->description ?? 'Нет описания' }}</dd>

                        @if($client->bluesales_id)
                            <dt class="col-sm-3">BlueSales ID:</dt>
                            <dd class="col-sm-9">
                                {{ $client->bluesales_id }}
                                @if($client->bluesales_last_sync)
                                    <br><small class="text-muted">Последняя синхронизация: {{ $client->bluesales_last_sync->format('d.m.Y H:i') }}</small>
                                @endif
                            </dd>
                        @endif
                    </dl>

                    <div class="mt-4">
                        <a href="{{ route('deals.create', ['client_id' => $client->id]) }}" class="btn btn-primary">
                            Создать сделку
                        </a>
                        <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                            Назад к списку клиентов
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection