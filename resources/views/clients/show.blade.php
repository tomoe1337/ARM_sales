@extends('layouts.app')

@section('title', 'Информация о клиенте')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Информация о клиенте: {{ $client->name }}</div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Телефон:</dt>
                        <dd class="col-sm-9">{{ $client->phone ?? 'Не указан' }}</dd>

                        <dt class="col-sm-3">Email:</dt>
                        <dd class="col-sm-9">{{ $client->email ?? 'Не указан' }}</dd>

                        <dt class="col-sm-3">Адрес:</dt>
                        <dd class="col-sm-9">{{ $client->address ?? 'Не указан' }}</dd>

                        <dt class="col-sm-3">Описание:</dt>
                        <dd class="col-sm-9">{{ $client->description ?? 'Нет описания' }}</dd>
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