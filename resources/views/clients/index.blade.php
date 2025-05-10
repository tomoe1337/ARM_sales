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
                            <th>Телефон</th>
                            <th>Email</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>                        @foreach($deals as $client)
                            <tr>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->phone }}</td>
                                <td>{{ $client->email }}</td>
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