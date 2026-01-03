<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Отделы';

    protected static ?string $modelLabel = 'Отдел';

    protected static ?string $pluralModelLabel = 'Отделы';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация об отделе')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название отдела')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('head_id')
                            ->label('Руководитель отдела')
                            ->relationship('head', 'name', function (Builder $query, $get, $record) {
                                // Базовая фильтрация по организации
                                $query->where('organization_id', auth()->user()->organization_id);
                                
                                // При редактировании показываем только сотрудников этого отдела
                                if ($record && $record->id) {
                                    $query->where('department_id', $record->id);
                                }
                                // При создании показываем всех из организации
                                // (можно назначить руководителя, затем он переведет других в отдел)
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText(fn ($record) => 
                                $record?->id 
                                    ? 'Показаны только сотрудники данного отдела' 
                                    : 'После создания отдела перенесите сотрудников в него'
                            ),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ]),
                
                Forms\Components\Section::make('Настройки BlueSales')
                    ->description('Настройте автоматическую синхронизацию с BlueSales для этого отдела. Синхронизация выполняется автоматически каждые 5 минут.')
                    ->schema([
                        Forms\Components\Toggle::make('bluesales_credential.sync_enabled')
                            ->label('Включить автоматическую синхронизацию')
                            ->default(true)
                            ->helperText('При включении синхронизация будет выполняться автоматически каждые 5 минут')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('bluesales_credential.login')
                            ->label('Email для входа в BlueSales')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('example@mail.ru')
                            ->helperText('Email, который используется для входа в BlueSales')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('bluesales_credential.api_key')
                            ->label('API ключ BlueSales')
                            ->password()
                            ->maxLength(255)
                            ->helperText('API ключ можно получить в настройках вашего аккаунта BlueSales')
                            ->placeholder(function ($record) {
                                $hasKey = $record?->bluesalesCredential?->api_key ?? false;
                                return $hasKey ? '•••••••• (ключ уже сохранен, введите новый для изменения)' : 'Введите API ключ';
                            })
                            ->revealable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('head.name')
                    ->label('Руководитель')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Количество сотрудников')
                    ->counts('users')
                    ->sortable(),
                
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активен')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }

    /**
     * Фильтруем только отделы своей организации
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organization_id', auth()->user()->organization_id);
    }

    /**
     * Показываем только если пользователь имеет организацию
     */
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->organization_id !== null;
    }
}
