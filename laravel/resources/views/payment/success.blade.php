<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3;url={{ url('/admin/subscription-management') }}">
    <title>Оплата успешна</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Иконка успеха -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <!-- Заголовок -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Оплата успешна!
            </h1>
            
            <!-- Описание -->
            <p class="text-gray-600 mb-6">
                Ваша подписка будет активирована в течение нескольких минут.
            </p>
            
            <!-- Прогресс-бар -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-6">
                <div class="bg-green-600 h-2 rounded-full animate-progress" style="animation: progress 3s linear;"></div>
            </div>
            
            <!-- Кнопка -->
            <a href="{{ url('/admin/subscription-management') }}" 
               class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                Перейти к управлению подпиской
            </a>
            
            <!-- Таймер -->
            <p class="text-sm text-gray-500 mt-4">
                Автоматическое перенаправление через <span id="countdown">3</span> сек...
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
        let seconds = 3;
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

