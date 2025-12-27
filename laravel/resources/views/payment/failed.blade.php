<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5;url={{ url('/admin/subscription-management') }}">
    <title>Ошибка оплаты</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Иконка ошибки -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            
            <!-- Заголовок -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Оплата не прошла
            </h1>
            
            <!-- Описание -->
            <p class="text-gray-600 mb-6">
                К сожалению, платеж не был завершен. Пожалуйста, попробуйте снова.
            </p>
            
            <!-- Возможные причины -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <p class="text-sm font-semibold text-gray-700 mb-2">Возможные причины:</p>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Недостаточно средств на счете</li>
                    <li>• Ошибка при вводе данных карты</li>
                    <li>• Платеж был отменен</li>
                    <li>• Технический сбой</li>
                </ul>
            </div>
            
            <!-- Прогресс-бар -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-6">
                <div class="bg-red-600 h-2 rounded-full animate-progress" style="animation: progress 5s linear;"></div>
            </div>
            
            <!-- Кнопки -->
            <div class="space-y-3">
                <a href="{{ url('/admin/subscription-management') }}" 
                   class="block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                    Попробовать снова
                </a>
                
                <a href="{{ url('/admin') }}" 
                   class="block bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition">
                    Вернуться в админку
                </a>
            </div>
            
            <!-- Таймер -->
            <p class="text-sm text-gray-500 mt-4">
                Автоматическое перенаправление через <span id="countdown">5</span> сек...
            </p>
        </div>
    </div>

    <style>
        @keyframes progress {
            from { width: 0%; }
            to { width: 100%; }
        }
    </style>

    <script>
        let seconds = 5;
        const countdown = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            seconds--;
            countdown.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = '{{ url('/admin/subscription-management') }}';
            }
        }, 1000);
    </script>
</body>
</html>

