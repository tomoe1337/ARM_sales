@extends('layouts.app')

@section('title', 'Добавление задачи')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Добавление задачи</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Название</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(auth()->user()->isHead())
                        <div class="mb-3">
                            <label for="assignee_id" class="form-label">Исполнитель</label>
                            <select class="form-control @error('assignee_id') is-invalid @enderror" id="assignee_id" name="assignee_id" required>
                                <option value="">Выберите исполнителя</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('assignee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assignee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @else
                            <input type="hidden" name="assignee_id" value="{{ auth()->id() }}">
                        @endif

                        <div class="mb-3">
                            <label for="deadline" class="form-label">Срок выполнения</label>
                            <input type="date" class="form-control @error('deadline') is-invalid @enderror" id="deadline" name="deadline" value="{{ old('deadline') }}" required>
                            @error('deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="pending" {{ old('status') == 'new' ? 'selected' : '' }}>Новая</option>
                                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>В работе</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Выполнена</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Закрыта</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Сохранить
                            </button>
                            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
