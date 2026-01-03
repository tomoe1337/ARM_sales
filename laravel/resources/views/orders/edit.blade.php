@extends('layouts.app')

@section('title', 'Редактировать заказ')

@section('content')
    <div class="container">
        <h1>Редактировать заказ</h1>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-message">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-message">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('orders.update', $order) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="client_id" class="form-label">Клиент</label>
                <input type="text" class="form-control" value="{{ $order->client->name }} ({{ $order->client->phone }})" disabled>
                <input type="hidden" name="client_id" value="{{ $order->client_id }}">
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Статус <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="">Выберите статус</option>
                    <option value="new" {{ old('status', $order->status) == 'new' ? 'selected' : '' }}>Новый</option>
                    <option value="reserve" {{ old('status', $order->status) == 'reserve' ? 'selected' : '' }}>Резерв</option>
                    <option value="preorder" {{ old('status', $order->status) == 'preorder' ? 'selected' : '' }}>Предзаказ</option>
                    <option value="shipped" {{ old('status', $order->status) == 'shipped' ? 'selected' : '' }}>Отправлен</option>
                    <option value="delivered" {{ old('status', $order->status) == 'delivered' ? 'selected' : '' }}>Доставлен</option>
                    <option value="cancelled" {{ old('status', $order->status) == 'cancelled' ? 'selected' : '' }}>Отменен</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="user_id" class="form-label">Сотрудник</label>
                <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                    <option value="">Выберите сотрудника</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ (old('user_id', $order->user_id) == $user->id) ? 'selected' : '' }}>
                            {{ $user->full_name ?? 'Неизвестный пользователь' }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="discount" class="form-label">Скидка (в долях, например 0.1 = 10%)</label>
                <input type="number" step="0.0001" min="0" max="1" class="form-control @error('discount') is-invalid @enderror" id="discount" name="discount" value="{{ old('discount', $order->discount) }}">
                @error('discount')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="prepay" class="form-label">Предоплата</label>
                <input type="text" class="form-control price-input @error('prepay') is-invalid @enderror" id="prepay" name="prepay" value="{{ old('prepay', $order->prepay ? number_format($order->prepay, 0, '', ' ') : '') }}">
                @error('prepay')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="customer_comments" class="form-label">Комментарии клиента</label>
                <textarea class="form-control @error('customer_comments') is-invalid @enderror" id="customer_comments" name="customer_comments" rows="3">{{ old('customer_comments', $order->customer_comments) }}</textarea>
                @error('customer_comments')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="internal_comments" class="form-label">Внутренние комментарии</label>
                <textarea class="form-control @error('internal_comments') is-invalid @enderror" id="internal_comments" name="internal_comments" rows="3">{{ old('internal_comments', $order->internal_comments) }}</textarea>
                @error('internal_comments')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Товары заказа -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Товары в заказе <span class="text-danger">*</span></h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-item-btn">
                        <i class="bi bi-plus-circle"></i> Добавить товар
                    </button>
                </div>
                
                <div id="order-items-container">
                    @php
                        $oldItems = old('order_items', $order->orderItems->map(function($item) {
                            return [
                                'id' => $item->id,
                                'product_name' => $item->product_name,
                                'product_marking' => $item->product_marking,
                                'price' => $item->price,
                                'quantity' => $item->quantity,
                            ];
                        })->toArray());
                        if (empty($oldItems)) {
                            $oldItems = [['product_name' => '', 'price' => '', 'quantity' => 1]];
                        }
                    @endphp
                    
                    @foreach($oldItems as $index => $item)
                        <div class="card mb-3 order-item" data-index="{{ $index }}">
                            <div class="card-body">
                                @if(isset($item['id']))
                                    <input type="hidden" name="order_items[{{ $index }}][id]" value="{{ $item['id'] }}">
                                @endif
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="form-label">Название товара <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error("order_items.{$index}.product_name") is-invalid @enderror" 
                                               name="order_items[{{ $index }}][product_name]" 
                                               value="{{ $item['product_name'] ?? '' }}" 
                                               required>
                                        @error("order_items.{$index}.product_name")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Цена <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control item-price price-input @error("order_items.{$index}.price") is-invalid @enderror" 
                                               name="order_items[{{ $index }}][price]" 
                                               value="{{ isset($item['price']) && is_numeric($item['price']) ? number_format((float)$item['price'], 0, '', ' ') : ($item['price'] ?? '') }}" 
                                               required>
                                        @error("order_items.{$index}.price")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Количество <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               class="form-control item-quantity @error("order_items.{$index}.quantity") is-invalid @enderror" 
                                               name="order_items[{{ $index }}][quantity]" 
                                               value="{{ $item['quantity'] ?? 1 }}" 
                                               required min="1">
                                        @error("order_items.{$index}.quantity")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Артикул</label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="order_items[{{ $index }}][product_marking]" 
                                               value="{{ $item['product_marking'] ?? '' }}">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">&nbsp;</label>
                                        @if(count($oldItems) > 1)
                                            <button type="button" class="btn btn-outline-danger w-100 remove-item-btn" title="Удалить товар">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-secondary w-100 remove-item-btn" disabled title="Нельзя удалить единственный товар">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Итоговая сумма (только для отображения) -->
            <div class="mb-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Итоговая сумма:</h5>
                            <h4 class="mb-0 text-primary" id="total-amount-display">0 ₽</h4>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Обновить заказ</button>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemIndex = {{ count($oldItems) }};
            const container = document.getElementById('order-items-container');
            const addBtn = document.getElementById('add-item-btn');
            const totalAmountDisplay = document.getElementById('total-amount-display');

            // Добавление товара
            addBtn.addEventListener('click', function() {
                const itemHtml = `
                    <div class="card mb-3 order-item" data-index="${itemIndex}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <label class="form-label">Название товара <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="order_items[${itemIndex}][product_name]" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Цена <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control item-price price-input" name="order_items[${itemIndex}][price]" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Количество <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control item-quantity" name="order_items[${itemIndex}][quantity]" value="1" required min="1">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Артикул</label>
                                    <input type="text" class="form-control" name="order_items[${itemIndex}][product_marking]">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-outline-danger w-100 remove-item-btn" title="Удалить товар">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                            <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', itemHtml);
                itemIndex++;
                updateRemoveButtons();
            });

            // Удаление товара
            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-item-btn')) {
                    const item = e.target.closest('.order-item');
                    item.remove();
                    updateRemoveButtons();
                    calculateTotal();
                }
            });

            // Форматирование чисел с пробелами
            function formatNumber(value) {
                let num = value.toString().replace(/[^\d.,]/g, '');
                num = num.replace(',', '.');
                num = num.replace(/[^\d.]/g, '');
                const parts = num.split('.');
                if (parts.length > 2) {
                    num = parts[0] + '.' + parts.slice(1).join('');
                }
                return num;
            }

            function formatNumberWithSpaces(value) {
                const num = formatNumber(value);
                if (!num) return '';
                
                const parts = num.split('.');
                const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                return parts.length > 1 ? integerPart + '.' + parts[1] : integerPart;
            }

            function getNumericValue(value) {
                return parseFloat(formatNumber(value)) || 0;
            }

            // Обработка форматирования полей цены
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('price-input')) {
                    const cursorPos = e.target.selectionStart;
                    const oldValue = e.target.value;
                    const formatted = formatNumberWithSpaces(e.target.value);
                    e.target.value = formatted;
                    
                    const diff = formatted.length - oldValue.length;
                    e.target.setSelectionRange(cursorPos + diff, cursorPos + diff);
                }
            });

            // При потере фокуса форматируем
            document.addEventListener('blur', function(e) {
                if (e.target.classList.contains('price-input')) {
                    e.target.value = formatNumberWithSpaces(e.target.value);
                }
            }, true);

            // Пересчет общей суммы
            container.addEventListener('input', function(e) {
                if (e.target.classList.contains('item-price') || e.target.classList.contains('item-quantity')) {
                    calculateTotal();
                }
            });

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.order-item').forEach(function(item) {
                    const priceInput = item.querySelector('.item-price');
                    const price = getNumericValue(priceInput.value);
                    const quantity = parseInt(item.querySelector('.item-quantity').value) || 0;
                    total += price * quantity;
                });
                totalAmountDisplay.textContent = formatNumberWithSpaces(total.toFixed(2)) + ' ₽';
            }

            // Перед отправкой формы убираем пробелы из значений
            document.querySelector('form').addEventListener('submit', function(e) {
                document.querySelectorAll('.price-input').forEach(function(input) {
                    const numericValue = getNumericValue(input.value);
                    input.value = numericValue.toString();
                });
            });

            function updateRemoveButtons() {
                const items = container.querySelectorAll('.order-item');
                items.forEach(function(item) {
                    const btn = item.querySelector('.remove-item-btn');
                    if (items.length === 1) {
                        btn.disabled = true;
                        btn.classList.remove('btn-outline-danger');
                        btn.classList.add('btn-outline-secondary');
                        btn.title = 'Нельзя удалить единственный товар';
                    } else {
                        btn.disabled = false;
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('btn-outline-danger');
                        btn.title = 'Удалить товар';
                    }
                });
            }

            // Инициализация
            calculateTotal();
            updateRemoveButtons();
        });
    </script>
    @endpush
@endsection
