<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-building-office-2" class="w-5 h-5" />
                    <span>Информация об организации</span>
                </div>
                <button 
                    type="button"
                    wire:click="$set('isCollapsed', {{ $isCollapsed ? 'false' : 'true' }})"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                >
                    <x-filament::icon 
                        :icon="$isCollapsed ? 'heroicon-o-chevron-down' : 'heroicon-o-chevron-up'" 
                        class="w-5 h-5" 
                    />
                </button>
            </div>
        </x-slot>

        <x-slot name="description">
            Управление данными вашей организации
        </x-slot>

        @if(!$isCollapsed)
            <form wire:submit="save" class="space-y-6">
                {{ $this->form }}

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button 
                        type="submit" 
                        color="primary"
                        size="md"
                        icon="heroicon-o-check"
                    >
                        Сохранить изменения
                    </x-filament::button>
                </div>
            </form>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
