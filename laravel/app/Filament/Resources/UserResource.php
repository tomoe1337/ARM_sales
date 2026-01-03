<?php

namespace App\Filament\Resources;

use App\Enums\UserRolesEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Менеджеры';

    protected static ?string $modelLabel = 'Менеджер';

    protected static ?string $pluralModelLabel = 'Менеджеры';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
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
                                $user = auth()->user();
                                return $rule->where('organization_id', $user->organization_id);
                            }),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof \App\Filament\Resources\UserResource\Pages\CreateUser)
                            ->minLength(8)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->helperText('Минимум 8 символов'),
                        
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
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Фильтруем только пользователей своей организации
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        
        $query = parent::getEloquentQuery()
            ->where('organization_id', $user->organization_id);
        
        // Руководитель видит всех пользователей своего отдела
        if ($user->isHead()) {
            $query->where('department_id', $user->department_id);
        }
        
        return $query;
    }

    /**
     * Показываем только если пользователь имеет организацию
     * Скрываем из меню - используем только через RelationManager
     */
    public static function canViewAny(): bool
    {
        return false; // Скрываем из меню, используем только через RelationManager
    }
}
