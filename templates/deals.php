<?php
    session_start();
    require_once("../vendor/income_stats.php");
    require_once("../vendor/plan_stats.php");
    require_once("../vendor/deal_stats.php");


    $stats = deals_data($_SESSION['user']['id']);
    $income_today = htmlspecialchars($stats['income_today']);
    $income_this_month = htmlspecialchars($stats['income_this_month']);
    $count_deals_today = htmlspecialchars($stats['count_deals_today']);

    $plan_data = plan_data($_SESSION['user']['id']);

    $all_managers_data = $plan_data['all_managers_data'];
    $monthly_plan = htmlspecialchars($plan_data['monthly_plan']);


    $percentage_completed = (intval($income_this_month)/intval($monthly_plan))*100;

    $deals_list = get_deals_info();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр сделок</title>
    <link href="assets/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <a href="/">
        <button type="submit" class="btn btn-secondary btn-sm">Назад</button>
    </a>

    <div class="container py-4">

        <!-- Графики -->
        <div class="chart-container">
            <h3>Статистика продаж</h3>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Сумма продаж сегодня</h5>
                            <p class="text-center display-6"><?=$income_today?> ₽</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">План сотрудника на месяц</h5>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?=$percentage_completed?>%;" aria-valuenow="<?=$percentage_completed ?>" aria-valuemin="0" aria-valuemax="100"><?=round($percentage_completed,2)?>%</div>
                            </div>
                            <p class="mt-2"><?=$income_this_month?> ₽ из <?=$monthly_plan?> ₽</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Общая сумма продаж</h5>
                            <p class="text-center display-6"><?=$income_this_month?> ₽</p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>

        <h1>Сделки</h1>

        <!-- Форма добавления новой сделки -->
        <div class="card mb-4">
            <div class="card-header">Добавить новую сделку</div>
            <div class="card-body">
                <form action="../vendor/add_deal.php" method="POST">
                    <div class="mb-3">
                        <label for="dealAmount" class="form-label">Сумма сделки (₽)</label>
                        <input name = "amount" type="number" class="form-control" id="dealAmount" required>
                    </div>
                    <div class="mb-3">
                        <label for="dealProduct" class="form-label">Продукт</label>
                        <input name = "product" type="text" class="form-control" id="dealProduct" required>
                    </div>
                    <div class="mb-3">
                        <label for="customerId" class="form-label">ID покупателя</label>
                        <input name = "client_id" type="number" class="form-control" id="customerId" required>
                    </div>
                    <div class="mb-3">
                        <label for="saleDate" class="form-label">Дата продажи</label>
                        <input name = "date" type="date" class="form-control" id="saleDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="dealComment" class="form-label">Комментарий</label>
                        <textarea name = "comments" class="form-control" id="dealComment" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Сохранить</button>
                </form>
            </div>
        </div>

        <!-- Таблица сделок -->
        <div class="info-box">
            <h3>Список клиентов</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Сумма сделки</th>
                            <th>Продукт</th>
                            <th>ID покупателя</th>
                            <th>Дата продажи</th>
                            <th>Комментарий</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php

                        while ($data = mysqli_fetch_assoc($deals_list)){
                    ?>
                        <tr>

                            <td><?=htmlspecialchars($data["id"])?></td>
                            <td><?=htmlspecialchars($data["amount"])?></td>
                            <td><?=htmlspecialchars($data["product"])?></td>
                            <td><?=htmlspecialchars($data["client_id"])?></td>
                            <td><?=htmlspecialchars($data["date"])?></td>
                            <td><?=htmlspecialchars($data["comments"])?></td>
                        </tr>
                    <?php
                        }
                    ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
