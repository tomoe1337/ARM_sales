@extends('layouts.app')

@section('title', 'Задачи')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Задачи</h1>
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">Добавить задачу</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($tasks->isEmpty())
                        <div class="alert alert-info">
                            У вас пока нет задач. <a href="{{ route('tasks.create') }}">Создайте первую задачу</a>.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Исполнитель</th>
                                        <th>Срок выполнения</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tasks as $task)
                                        <tr>
                                            <td>{{ $task->title }}</td>
                                            <td>{{ $task->assignee?->full_name ?? 'Не назначен' }}</td>
                                            <td>{{ $task->deadline->format('d.m.Y') }}</td>
                                            <td>
                                                @switch($task->status)
                                                    @case('pending')
                                                        <span class="badge bg-secondary">Новая</span>
                                                        @break
                                                    @case('in_progress')
                                                        <span class="badge bg-primary">В работе</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">Выполнена</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">Отменена</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-info" title="Просмотр">
                                                        <i class="fas fa-eye"></i> Просмотр
                                                    </a>
                                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-primary" title="Редактировать">
                                                        <i class="fas fa-edit"></i> Редактировать
                                                    </a>
                                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту задачу?')" title="Удалить">
                                                            <i class="fas fa-trash"></i> Удалить
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 