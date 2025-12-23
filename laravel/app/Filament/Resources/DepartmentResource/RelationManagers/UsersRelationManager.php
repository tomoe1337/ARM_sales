<?php

namespace App\Filament\Resources\DepartmentResource\RelationManagers;

use App\Enums\UserRolesEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
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
                        
                        Forms\Components\TextInput::make('login')
                            ->label('Логин')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                $department = $this->getOwnerRecord();
                                return $rule->where('organization_id', $department->organization_id);
                            }),
                        
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
                            ->options([
                                UserRolesEnum::MANAGER->value => 'Менеджер',
                                UserRolesEnum::HEAD->value => 'Руководитель отдела',
                            ])
                            ->default(UserRolesEnum::MANAGER->value)
                            ->required()
                            ->disabled(fn ($record) => $record && $record->id === auth()->id()),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
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
                
                Tables\Columns\TextColumn::make('login')
                    ->label('Логин')
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
                    ->options([
                        UserRolesEnum::MANAGER->value => 'Менеджер',
                        UserRolesEnum::HEAD->value => 'Руководитель',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активен')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить менеджера')
                    ->mutateFormDataUsing(function (array $data): array {
                        $department = $this->getOwnerRecord();
                        
                        // Автоматически привязываем к организации и отделу
                        $data['organization_id'] = $department->organization_id;
                        $data['department_id'] = $department->id;
                        
                        // По умолчанию создаем менеджера
                        if (!isset($data['role'])) {
                            $data['role'] = UserRolesEnum::MANAGER->value;
                        }
                        
                        // Активируем пользователя
                        $data['is_active'] = true;
                        $data['activated_at'] = now();
                        
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
