<?php

?>


 <!-- Форма добавления нового клиента -->
        <div class="info-box">
            <h3>Добавить нового клиента</h3>
            <form action="/vendor/add_client.php" method="POST">
                <div class="mb-3">
                    <label for="clientName" class="form-label">Имя</label>
                    <input name = "full_name" type="text" class="form-control" id="clientName" placeholder="Введите имя" required>
                </div>
                <div class="mb-3">
                    <label for="clientPhone" class="form-label">Контактный номер</label>
                    <input name = "phone" type="tel" class="form-control" id="clientPhone" placeholder="Введите номер телефона" required>
                </div>
                <div class="mb-3">
                    <label for="clientEmail" class="form-label">Email</label>
                    <input name = "email" type="email" class="form-control" id="clientEmail" placeholder="Введите email" required>
                </div>
                <div class="mb-3">
                    <label for="lastContactDate" class="form-label">Дата последнего контакта</label>
                    <input name = "last_contact_date" type="date" class="form-control" id="lastContactDate">
                </div>
                <div class="mb-3">
                    <label for="dealStatus" class="form-label">Статус сделки</label>
                    <select name = "status" class="form-control" id="dealStatus">
                        <option value="">Выберите статус</option>
                        <option value="Создан">Создан</option>
                        <option value="closed">КП отправлено</option>
                        <option value="lost">Переговоры</option>
                        <option value="lost">Принимает решение</option>
                        <option value="lost">Успешная сделка</option>
                        <option value="lost">Отказ</option>


                    </select>
                </div>
                <div class="mb-3">
                    <label for="clientComment" class="form-label">Комментарий</label>
                    <textarea name = "comments" class="form-control" id="clientComment" rows="3" placeholder="Введите комментарий"></textarea>
                </div>
                <button type="submit" class="btn btn-success">Добавить клиента</button>
            </form>
        </div>
    </div>
