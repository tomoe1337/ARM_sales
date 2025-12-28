<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Выбор отдела через табы --}}
        @php
            $departments = \App\Models\Department::where('organization_id', auth()->user()->organization_id)
                ->where('is_active', true)
                ->get();
            $hasMultipleDepartments = $departments->count() > 1;
        @endphp

        @if($hasMultipleDepartments)
            <div class="fi-tabs flex overflow-x-auto">
                <nav class="flex space-x-1" aria-label="Tabs">
                    <button
                        wire:click="$set('selectedDepartmentId', null)"
                        type="button"
                        class="@if($selectedDepartmentId === null) bg-primary-500 text-white @else bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 @endif px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Все отделы
                    </button>
                    @foreach($departments as $department)
                        <button
                            wire:click="$set('selectedDepartmentId', {{ $department->id }})"
                            type="button"
                            class="@if($selectedDepartmentId === $department->id) bg-primary-500 text-white @else bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 @endif px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            {{ $department->name }}
                        </button>
                    @endforeach
                </nav>
            </div>

            @if($selectedDepartmentId === null)
                @php
                    $allSubscriptions = \App\Models\Subscription::whereIn('department_id', $departments->pluck('id'))
                        ->with('department', 'plan')
                        ->get();
                    $totalAmount = $allSubscriptions->sum('monthly_price');
                    $totalActiveUsers = 0;
                    $totalLimit = 0;
                    $hasUnpaidDepartments = false;
                    foreach ($allSubscriptions as $sub) {
                        $totalActiveUsers += $sub->getActivePaidUsersCount();
                        $totalLimit += $sub->paid_users_limit;
                        if (!$sub->isActive() && $sub->monthly_price > 0) {
                            $hasUnpaidDepartments = true;
                        }
                    }
                @endphp

                <x-filament::section>
                    <x-slot name="heading">
                        Все отделы
                    </x-slot>

                    <x-slot name="description">
                        Общая информация по всем отделам организации
                    </x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Отделов</div>
                            <div class="text-lg font-semibold mt-1">{{ $departments->count() }}</div>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Общий лимит пользователей</div>
                            <div class="text-lg font-semibold mt-1">{{ $totalLimit }}</div>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Активных пользователей</div>
                            <div class="text-lg font-semibold mt-1">{{ $totalActiveUsers }} / {{ $totalLimit }}</div>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Общая стоимость в месяц</div>
                            <div class="text-lg font-semibold mt-1">
                                {{ number_format($totalAmount, 0, ',', ' ') }}₽
                            </div>
                        </div>
                    </div>

                    @if($hasUnpaidDepartments)
                        <div class="mt-4">
                            <x-filament::button 
                                wire:click="payAllDepartments"
                                wire:confirm="Вы уверены, что хотите оплатить все отделы?"
                                color="success"
                                icon="heroicon-o-credit-card"
                                size="lg">
                                Оплатить все отделы ({{ number_format($totalAmount, 0, ',', ' ') }}₽)
                            </x-filament::button>
                        </div>
                    @endif
                </x-filament::section>
            @endif
        @endif

        {{-- Статус подписки (только если выбран отдел) --}}
        @if($selectedDepartmentId)
        <x-filament::section>
            <x-slot name="heading">
                Статус подписки
            </x-slot>

            <x-slot name="description">
                Информация о текущей подписке вашего отдела
            </x-slot>

            @php
                $status = $this->getSubscriptionStatus();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Тарифный план</div>
                    <div class="text-lg font-semibold mt-1">
                        {{ $this->subscription?->plan->name ?? 'Не выбран' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ number_format($status['price_per_user'], 0, ',', ' ') }}₽/пользователь
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Статус</div>
                    <div class="text-lg font-semibold mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($this->subscription && $this->subscription->isTrial()) bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @elseif($this->subscription && $this->subscription->isActive()) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @endif">
                            {{ $status['status_label'] }}
                        </span>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Лимит пользователей</div>
                    <div class="text-lg font-semibold mt-1">{{ $status['paid_users_limit'] }}</div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Активных пользователей</div>
                    <div class="text-lg font-semibold mt-1">
                        {{ $status['active_users_count'] }} / {{ $status['paid_users_limit'] }}
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Стоимость в месяц</div>
                    <div class="text-lg font-semibold mt-1">
                        {{ number_format($status['monthly_price'], 0, ',', ' ') }}₽
                    </div>
                </div>
            </div>

            @if($status['ends_at'])
                <div class="mt-4 p-3 @if($this->subscription && $this->subscription->isExpired()) bg-red-50 dark:bg-red-900/20 @else bg-blue-50 dark:bg-blue-900/20 @endif rounded-lg">
                    <div class="text-sm @if($this->subscription && $this->subscription->isExpired()) text-red-700 dark:text-red-300 @else text-gray-700 dark:text-gray-300 @endif">
                        @if($this->subscription && $this->subscription->isTrial())
                            Пробный период до: <strong>{{ $status['trial_ends_at']->format('d.m.Y') }}</strong>
                        @elseif($this->subscription && $this->subscription->trial_ends_at && $this->subscription->trial_ends_at->isPast())
                            Пробный период закончился: <strong>{{ $this->subscription->trial_ends_at->format('d.m.Y') }}</strong>
                        @elseif($this->subscription && $this->subscription->isExpired())
                            Подписка истекла: <strong>{{ $status['ends_at']->format('d.m.Y') }}</strong>
                        @else
                            Подписка действует до: <strong>{{ $status['ends_at']->format('d.m.Y') }}</strong>
                        @endif
                    </div>
                </div>
            @endif

        </x-filament::section>
        @endif

        {{-- Форма продления подписки (только если выбран отдел) --}}
        @if($selectedDepartmentId)
            {{ $this->form }}
        @endif

        {{-- Список пользователей (только если выбран отдел) --}}
        @if($selectedDepartmentId)
        <x-filament::section>
            <x-slot name="heading">
                Пользователи отдела
            </x-slot>

            <x-slot name="description">
                Управление доступом пользователей вашего отдела
            </x-slot>

            {{ $this->table }}
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
