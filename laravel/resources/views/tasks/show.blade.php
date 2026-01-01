@extends('layouts.app')

@section('title', 'Просмотр задачи')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Просмотр задачи</h1>
                <div class="btn-group">
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Редактировать
                    </a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту задачу?')">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">Основная информация</h5>
                            <table class="table">
                                <tr>
                                    <th>Название:</th>
                                    <td>{{ $task->title }}</td>
                                </tr>
                                <tr>
                                    <th>Срок выполнения:</th>
                                    <td>{{ $task->deadline->format('d.m.Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Статус:</th>
                                    <td>
                                        @switch($task->status)
                                            @case('new')
                                                <span class="badge bg-secondary">Новая</span>
                                                @break
                                            @case('in_progress')
                                                <span class="badge bg-primary">В работе</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">Выполнена</span>
                                                @break
                                        @endswitch
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Описание</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $task->description ?: 'Описание отсутствует' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>
</div>
@endsection 