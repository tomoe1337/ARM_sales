<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - AI-аналитика для BlueSales CRM</title>
    <meta name="description" content="Увеличьте эффективность отдела продаж с AI-аналитикой. Надстройка для BlueSales CRM с автоматическими отчетами и умными рекомендациями.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    <!-- Styles -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            .animate-float {
                animation: float 6s ease-in-out infinite;
            }
            .gradient-text {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
        </style>
    @endif
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-white/80 backdrop-blur-md border-b border-gray-200 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold gradient-text">{{ config('app.name') }}</span>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition">
                            Дашборд
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition">
                            Войти
                        </a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition shadow-lg hover:shadow-xl">
                            Начать бесплатно
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-block mb-4 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">
                        🤖 AI-аналитика для BlueSales
                    </div>
                    <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        Увеличьте продажи на
                        <span class="gradient-text">30%</span>
                        с AI-аналитикой
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Автоматические отчеты, умные рекомендации и полный контроль над отделом продаж. 
                        Работает поверх вашей BlueSales CRM.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl transform hover:scale-105">
                            Попробовать бесплатно 14 дней →
                        </a>
                        <a href="#features" class="border-2 border-gray-300 text-gray-700 px-8 py-4 rounded-lg text-lg font-semibold hover:border-indigo-600 hover:text-indigo-600 transition">
                            Узнать больше
                        </a>
                    </div>
                    <div class="mt-8 flex items-center justify-center lg:justify-start gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>14 дней бесплатно</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Без кредитной карты</span>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="relative z-10 animate-float">
                        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-8 shadow-2xl">
                            <div class="bg-white rounded-lg p-6 space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">AI Отчет за неделю</h3>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Готово</span>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-gray-600">Конверсия лидов</span>
                                        <span class="font-bold text-indigo-600">+23%</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-gray-600">Средний чек</span>
                                        <span class="font-bold text-indigo-600">+15%</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-gray-600">Время сделки</span>
                                        <span class="font-bold text-indigo-600">-18%</span>
                                    </div>
                                </div>
                                <div class="pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-600">💡 Рекомендация: Увеличьте активность в первой половине дня</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -top-4 -right-4 w-72 h-72 bg-indigo-200 rounded-full opacity-20 blur-3xl"></div>
                    <div class="absolute -bottom-4 -left-4 w-72 h-72 bg-purple-200 rounded-full opacity-20 blur-3xl"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problem Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Тратите часы на ручные отчеты?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Большинство руководителей отделов продаж тратят до 8 часов в неделю на подготовку отчетов. 
                    Мы автоматизируем этот процесс с помощью AI.
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-lg">
                    <div class="text-4xl mb-4">⏰</div>
                    <h3 class="text-xl font-semibold mb-2">Тратите время на рутину</h3>
                    <p class="text-gray-600">
                        Менеджеры тратят <strong>5 часов в неделю</strong> на подготовку отчетов вместо звонков клиентам. 
                        Руководитель тратит <strong>8 часов в неделю</strong> на анализ данных вручную.
                    </p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-lg">
                    <div class="text-4xl mb-4">📊</div>
                    <h3 class="text-xl font-semibold mb-2">Не видите проблемные точки</h3>
                    <p class="text-gray-600">
                        Не знаете, почему <strong>30% лидов не конвертируются</strong> в сделки. 
                        Не видите, что менеджер Петров закрывает сделки в <strong>2 раза быстрее</strong>, чем менеджер Иванов.
                    </p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-lg">
                    <div class="text-4xl mb-4">🤔</div>
                    <h3 class="text-xl font-semibold mb-2">Решения без данных</h3>
                    <p class="text-gray-600">
                        Увеличиваете план продаж <strong>на 20%</strong>, не зная реальных возможностей отдела. 
                        Назначаете бонусы <strong>наугад</strong>, вместо анализа эффективности каждого менеджера.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Solution Section -->
    <section id="features" class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Решение: AI-аналитика для BlueSales</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Единственная надстройка для BlueSales с искусственным интеллектом, 
                    которая автоматически анализирует работу отдела и дает конкретные рекомендации
                </p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="p-8 rounded-xl border-2 border-gray-200 hover:border-indigo-500 transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">AI-аналитика эффективности</h3>
                    <p class="text-gray-600">Автоматические еженедельные отчеты с анализом конверсии лидов в заказы и рекомендациями по улучшению работы</p>
                </div>
                <div class="p-8 rounded-xl border-2 border-gray-200 hover:border-indigo-500 transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Планирование и контроль</h3>
                    <p class="text-gray-600">Месячные и дневные планы продаж, отслеживание выполнения и контроль рабочего времени сотрудников</p>
                </div>
                <div class="p-8 rounded-xl border-2 border-gray-200 hover:border-indigo-500 transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Интеграция с BlueSales</h3>
                    <p class="text-gray-600">Автоматическая синхронизация клиентов и заказов. Работает поверх вашей CRM без дублирования данных</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-indigo-50 to-purple-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Как это работает</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Всего 3 простых шага до увеличения продаж
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h3 class="text-xl font-semibold mb-2">Подключите {{ config('app.name') }}</h3>
                    <p class="text-gray-600">Подключите систему к вашему BlueSales CRM. Это займет всего 5 минут</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h3 class="text-xl font-semibold mb-2">AI анализирует данные</h3>
                    <p class="text-gray-600">Система автоматически анализирует работу отдела продаж и выявляет точки роста</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h3 class="text-xl font-semibold mb-2">Получайте отчеты</h3>
                    <p class="text-gray-600">Каждую неделю получайте автоматические отчеты с конкретными рекомендациями по улучшению</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Простая и прозрачная цена</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Всего {{ number_format($standardPlan->price_per_user, 0, ',', ' ') }}₽ в месяц за сотрудника. 
                    BlueSales ({{ number_format(config('pricing.bluesales_basic_price', 999), 0, ',', ' ') }}₽) + 
                    {{ config('app.name') }} ({{ number_format($standardPlan->price_per_user, 0, ',', ' ') }}₽) = 
                    {{ number_format(config('pricing.bluesales_basic_price', 999) + $standardPlan->price_per_user, 0, ',', ' ') }}₽/мес
                </p>
            </div>
            <div class="max-w-2xl mx-auto">
                <div class="bg-white border-2 border-indigo-500 rounded-2xl p-8 shadow-xl">
                    <div class="text-center mb-8">
                        <div class="inline-block px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium mb-4">
                            Рекомендуется
                        </div>
                        <h3 class="text-3xl font-bold mb-2">{{ $standardPlan->name }}</h3>
                        <div class="flex items-baseline justify-center gap-2 mb-4">
                            <span class="text-5xl font-bold gradient-text">{{ number_format($standardPlan->price_per_user, 0, ',', ' ') }}₽</span>
                            <span class="text-gray-600">/мес за сотрудника</span>
                        </div>
                        @if($standardPlan->description)
                            <p class="text-gray-600">{{ $standardPlan->description }}</p>
                        @else
                            <p class="text-gray-600">Все функции включены</p>
                        @endif
                    </div>
                    <ul class="space-y-4 mb-8">
                        @if($standardPlan->ai_analytics_enabled)
                        <li class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>AI-аналитика с еженедельными отчетами</span>
                        </li>
                        @endif
                        @if($standardPlan->crm_sync_enabled)
                        <li class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Интеграция с BlueSales CRM</span>
                        </li>
                        @endif
                        <li class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Планирование продаж и контроль</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>14 дней бесплатного пробного периода</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full bg-indigo-600 text-white text-center px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl">
                        Начать бесплатно
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-indigo-600 to-purple-600">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-bold text-white mb-4">Готовы увеличить продажи?</h2>
            <p class="text-xl text-indigo-100 mb-8">
                Присоединяйтесь к компаниям, которые уже используют AI-аналитику для роста продаж
            </p>
            <a href="{{ route('register') }}" class="inline-block bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition shadow-xl hover:shadow-2xl transform hover:scale-105">
                Начать бесплатный пробный период →
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-4 sm:px-6 lg:px-8 bg-gray-900 text-gray-400">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h3 class="text-white text-xl font-bold mb-4">{{ config('app.name') }}</h3>
                    <p class="text-sm">AI-аналитика для BlueSales CRM. Увеличьте эффективность отдела продаж с помощью искусственного интеллекта.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Продукт</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-white transition">Возможности</a></li>
                        <li><a href="#" class="hover:text-white transition">Цены</a></li>
                        <li><a href="#" class="hover:text-white transition">Интеграции</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Поддержка</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">Документация</a></li>
                        <li><a href="#" class="hover:text-white transition">Контакты</a></li>
                        <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Все права защищены.</p>
            </div>
        </div>
    </footer>
</body>
</html>

