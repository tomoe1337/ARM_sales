<?php

namespace App\Filament\Widgets;

use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class OrganizationInfoWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.organization-info-widget';

    protected int | string | array $columnSpan = 'full';

    public bool $isCollapsed = false;

    public ?array $data = [];

    public ?Organization $organization = null;

    public function mount(): void
    {
        $this->organization = Auth::user()->organization;
        
        if ($this->organization) {
            $this->form->fill([
                'name' => $this->organization->name,
                'email' => $this->organization->email,
                'phone' => $this->organization->phone,
                'address' => $this->organization->address,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название организации')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Введите название организации')
                    ->columnSpanFull(),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('email@example.com'),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('+7 (999) 123-45-67'),
                    ]),
                
                Forms\Components\Textarea::make('address')
                    ->label('Адрес')
                    ->rows(2)
                    ->maxLength(500)
                    ->placeholder('Город, улица, дом')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        if (!$this->organization) {
            return;
        }

        $data = $this->form->getState();
        $this->organization->update($data);

        Notification::make()
            ->success()
            ->title('Настройки организации обновлены')
            ->send();
    }

    protected function getCachedFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Сохранить')
                ->submit('save'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }
}

