<?php

namespace App\Filament\Resources\DepartmentResource\RelationManagers;

use Filament\Forms;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Менеджеры отдела';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('full_name')
                            ->label('Фамилия')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                $department = $this->getOwnerRecord();
                                return $rule->where('organization_id', $department->organization_id);
                            }),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->required(fn ($record) => !$record || !$record->exists)
                            ->minLength(8)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->helperText('Минимум 8 символов')
                            ->visible(fn ($record) => !$record || !$record->exists),
                        
                        Forms\Components\Select::make('role')
                            ->label('Роль')
                            ->options(function () {
                                return Role::whereIn('name', ['manager', 'head'])
                                    ->pluck('name', 'name')
                                    ->map(fn($name) => match($name) {
                                        'head' => 'Руководитель отдела',
                                        'manager' => 'Менеджер',
                                        default => $name,
                                    });
                            })
                            ->default('manager')
                            ->required()
                            ->disabled(fn ($record) => $record && $record->id === auth()->id())
                            ->afterStateUpdated(function ($state, $record) {
                                if ($record && $state) {
                                    $record->syncRoles([$state]);
                                }
                            }),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Фамилия')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('role')
                    ->label('Роль')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'head' => 'success',
                        'manager' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'head' => 'Руководитель',
                        'manager' => 'Менеджер',
                        default => $state,
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Роль')
                    ->options(function () {
                        return Role::whereIn('name', ['manager', 'head'])
                            ->pluck('name', 'name')
                            ->map(fn($name) => match($name) {
                                'head' => 'Руководитель',
                                'manager' => 'Менеджер',
                                default => $name,
                            });
                    }),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активен')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить менеджера')
                    ->using(function (array $data) {
                        $department = $this->getOwnerRecord();
                        
                        // Автоматически привязываем к организации и отделу
                        $data['organization_id'] = $department->organization_id;
                        $data['department_id'] = $department->id;
                        
                        // Создаем пользователя
                        $user = User::create($data);
                        
                        // Назначаем роль через Spatie
                        $role = $data['role'] ?? 'manager';
                        $user->assignRole($role);
                        
                        return $user;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $department = $this->getOwnerRecord();
                        
                        // Автоматически привязываем к организации и отделу
                        $data['organization_id'] = $department->organization_id;
                        $data['department_id'] = $department->id;
                        
                        // Проверяем лимит активных пользователей, если активируем
                        if (($data['is_active'] ?? false)) {
                            $subscription = $department->getActiveSubscription();
                            
                            // Если подписки нет или лимит = 0, нельзя создавать активных пользователей
                            if (!$subscription) {
                                $message = "Невозможно активировать пользователя. У отдела нет активной подписки или не оплачено ни одного пользователя.";
                                Notification::make()
                                    ->danger()
                                    ->title('Ошибка активации')
                                    ->body($message)
                                    ->send();
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'is_active' => $message
                                ]);
                            }
                            
                            // Если лимит = 0, нельзя создавать активных пользователей
                            if ($subscription->paid_users_limit <= 0) {
                                $message = "Невозможно активировать пользователя. Не оплачено ни одного пользователя в отделе. Оформите подписку для активации пользователей.";
                                Notification::make()
                                    ->danger()
                                    ->title('Ошибка активации')
                                    ->body($message)
                                    ->send();
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'is_active' => $message
                                ]);
                            }
                            
                            $activeUsersCount = $subscription->getActivePaidUsersCount();
                            // Учитываем нового пользователя в подсчете
                            $activeUsersCount++;
                            
                            if ($activeUsersCount > $subscription->paid_users_limit) {
                                $message = "Невозможно активировать пользователя. Достигнут лимит оплаченных пользователей ({$subscription->paid_users_limit}).";
                                Notification::make()
                                    ->danger()
                                    ->title('Ошибка активации')
                                    ->body($message)
                                    ->send();
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'is_active' => $message
                                ]);
                            }
                        }
                        
                        // Устанавливаем дату активации, если активируем
                        if ($data['is_active'] ?? false) {
                            $data['activated_at'] = now();
                        }
                        
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($record, array $data) {
                        // Синхронизируем роль через Spatie после обновления
                        if (isset($data['role'])) {
                            $record->syncRoles([$data['role']]);
                        }
                    })
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Проверяем лимит активных пользователей, если активируем (меняем с false на true)
                        if (($data['is_active'] ?? false) && !$record->is_active) {
                            $department = $this->getOwnerRecord();
                            $subscription = $department->getActiveSubscription();
                            
                            // Если подписки нет или лимит = 0, нельзя активировать пользователей
                            if (!$subscription) {
                                $message = "Невозможно активировать пользователя. У отдела нет активной подписки или не оплачено ни одного пользователя.";
                                Notification::make()
                                    ->danger()
                                    ->title('Ошибка активации')
                                    ->body($message)
                                    ->send();
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'is_active' => $message
                                ]);
                            }
                            
                            // Если лимит = 0, нельзя активировать пользователей
                            if ($subscription->paid_users_limit <= 0) {
                                $message = "Невозможно активировать пользователя. Не оплачено ни одного пользователя в отделе. Оформите подписку для активации пользователей.";
                                Notification::make()
                                    ->danger()
                                    ->title('Ошибка активации')
                                    ->body($message)
                                    ->send();
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'is_active' => $message
                                ]);
                            }
                            
                            $activeUsersCount = $subscription->getActivePaidUsersCount();
                            if ($activeUsersCount >= $subscription->paid_users_limit) {
                                $message = "Невозможно активировать пользователя. Достигнут лимит оплаченных пользователей ({$subscription->paid_users_limit}).";
                                Notification::make()
                                    ->danger()
                                    ->title('Ошибка активации')
                                    ->body($message)
                                    ->send();
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'is_active' => $message
                                ]);
                            }
                        }
                        
                        // Устанавливаем дату активации, если активируем
                        if (($data['is_active'] ?? false) && !$record->is_active) {
                            $data['activated_at'] = now();
                        } elseif (!($data['is_active'] ?? false) && $record->is_active) {
                            // Сбрасываем дату активации при деактивации
                            $data['activated_at'] = null;
                        }
                        
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
