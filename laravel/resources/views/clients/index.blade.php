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
                    <tbody>                        @foreach($clients as $client)
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
        </div>
    </div>
</div>
@endsection 