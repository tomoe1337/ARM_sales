@extends('layouts.app')

@section('title', 'Добавление клиента')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Добавление клиента</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('clients.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Полное имя</label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name') }}">
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Полное имя клиента (отличается от краткого имени)</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country" class="form-label">Страна</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">Город</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="birth_date" class="form-label">Дата рождения</label>
                                    <input type="date" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                                    @error('birth_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Пол</label>
                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender">
                                        <option value="">Не указан</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Мужской</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Женский</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Адрес</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- CRM поля -->
                        <h5 class="mt-4 mb-3">CRM информация</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="crm_status" class="form-label">Статус в CRM</label>
                                    <input type="text" class="form-control @error('crm_status') is-invalid @enderror" id="crm_status" name="crm_status" value="{{ old('crm_status') }}">
                                    @error('crm_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="source" class="form-label">Источник</label>
                                    <input type="text" class="form-control @error('source') is-invalid @enderror" id="source" name="source" value="{{ old('source') }}">
                                    @error('source')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="first_contact_date" class="form-label">Дата первого контакта</label>
                                    <input type="datetime-local" class="form-control @error('first_contact_date') is-invalid @enderror" id="first_contact_date" name="first_contact_date" value="{{ old('first_contact_date') }}">
                                    @error('first_contact_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="last_contact_date" class="form-label">Дата последнего контакта</label>
                                    <input type="datetime-local" class="form-control @error('last_contact_date') is-invalid @enderror" id="last_contact_date" name="last_contact_date" value="{{ old('last_contact_date') }}">
                                    @error('last_contact_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="next_contact_date" class="form-label">Дата следующего контакта</label>
                                    <input type="datetime-local" class="form-control @error('next_contact_date') is-invalid @enderror" id="next_contact_date" name="next_contact_date" value="{{ old('next_contact_date') }}">
                                    @error('next_contact_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sales_channel" class="form-label">Канал продаж</label>
                            <input type="text" class="form-control @error('sales_channel') is-invalid @enderror" id="sales_channel" name="sales_channel" value="{{ old('sales_channel') }}">
                            @error('sales_channel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Социальные сети -->
                        <h5 class="mt-4 mb-3">Социальные сети</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vk_id" class="form-label">VK ID</label>
                                    <input type="text" class="form-control @error('vk_id') is-invalid @enderror" id="vk_id" name="vk_id" value="{{ old('vk_id') }}">
                                    @error('vk_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ok_id" class="form-label">OK ID (Одноклассники)</label>
                                    <input type="text" class="form-control @error('ok_id') is-invalid @enderror" id="ok_id" name="ok_id" value="{{ old('ok_id') }}">
                                    @error('ok_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Теги</label>
                            <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags') }}">
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Теги через запятую</div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Заметки</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="additional_contacts" class="form-label">Дополнительные контакты</label>
                            <textarea class="form-control @error('additional_contacts') is-invalid @enderror" id="additional_contacts" name="additional_contacts" rows="2">{{ old('additional_contacts') }}</textarea>
                            @error('additional_contacts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Дополнительные телефоны, email и т.д.</div>
                        </div>
                        <div class="mb-3">
                            <label for="assignee_id" class="form-label">Исполнитель</label>
                            <select class="form-control @error('assignee_id') is-invalid @enderror" id="assignee_id" name="user_id" required>
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
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Добавить клиента</button>
                            <a href="{{ route('clients.index') }}" class="btn btn-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
