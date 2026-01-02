<?php

namespace App\Http\Controllers\Client;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Services\ClientService;

class StoreController
{
    protected $clientService;

    /**
     * Create a new controller instance.
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreClientRequest $request) 
    {
        $validatedData = $request->validated(); 

        try {
            $this->clientService->createClient($validatedData);

            return redirect()->route('clients.index')->with('success', 'Клиент успешно создан.');
        } catch (\Exception $e) {
            // Логируем ошибку для отладки
            \Log::error('Failed to create client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validatedData
            ]);

            // Формируем понятное сообщение для пользователя
            $errorMessage = 'Не удалось создать клиента.';
            
            // Если ошибка связана с BlueSales, делаем сообщение более понятным
            if (str_contains($e->getMessage(), 'BlueSales')) {
                $errorMessage = 'Не удалось создать клиента. Проблема с синхронизацией с BlueSales. Попробуйте позже или обратитесь к администратору.';
            } else {
                // Для других ошибок показываем общее сообщение
                $errorMessage = 'Не удалось создать клиента. Проверьте введенные данные и попробуйте снова.';
            }

            // Возвращаем пользователя на страницу создания с сообщением об ошибке
            return redirect()
                ->route('clients.create')
                ->withInput()
                ->with('error', $errorMessage);
        }
    }
}