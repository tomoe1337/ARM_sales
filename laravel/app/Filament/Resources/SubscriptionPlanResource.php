<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionPlanService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Тарифные планы';

    protected static ?string $modelLabel = 'Тарифный план';

    protected static ?string $pluralModelLabel = 'Тарифные планы';

    protected static ?string $navigationGroup = 'Система';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if (empty($set('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Уникальный идентификатор тарифа (латиница, дефисы)'),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Ценообразование')
                    ->schema([
                        Forms\Components\TextInput::make('price_per_user')
                            ->label('Цена за пользователя (₽/мес)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₽')
                            ->helperText('Стоимость за одного пользователя в месяц'),
                    ]),

                Forms\Components\Section::make('Функции')
                    ->schema([
                        Forms\Components\Toggle::make('ai_analytics_enabled')
                            ->label('AI-аналитика')
                            ->default(true)
                            ->helperText('Включить AI-аналитику для этого тарифа'),

                        Forms\Components\Toggle::make('crm_sync_enabled')
                            ->label('Синхронизация с CRM')
                            ->default(true)
                            ->helperText('Включить синхронизацию с BlueSales'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->helperText('Неактивные тарифы не будут доступны при выборе'),
                    ]),
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

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('price_per_user')
                    ->label('Цена за пользователя')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('ai_analytics_enabled')
                    ->label('AI')
                    ->boolean(),

                Tables\Columns\IconColumn::make('crm_sync_enabled')
                    ->label('CRM')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->label('Подписок')
                    ->counts('subscriptions')
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
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Удалить тарифный план?')
                    ->modalDescription('Это действие нельзя отменить. Тарифный план будет удален, если он не используется в активных подписках.')
                    ->action(function (SubscriptionPlan $record) {
                        app(SubscriptionPlanService::class)->delete($record, Auth::user());
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->isSuperAdmin();
    }
}

