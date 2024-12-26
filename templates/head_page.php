<?php 
    require_once ('../vendor/income_stats.php');
    require_once ('../vendor/plan_stats.php');
    require_once ('../vendor/clients_stats.php');


    session_start();
    $working_status = $_SESSION["user"]["status"] === "working";
    $time = date('H:i',strtotime("now -3 GMT"));



    $stats = deals_data();
    $income_today = $stats['income_today'];
    $income_this_month = htmlspecialchars($stats['income_this_month']);
    $count_deals_today = htmlspecialchars($stats['count_deals_today']);

    $plan_data = plan_data();

    $all_managers_data = $plan_data['all_managers_data'];
    $monthly_plan = htmlspecialchars($plan_data['monthly_plan']);

    $percentage_completed = (intval($income_this_month)/intval($monthly_plan))*100;


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>АРМ Руководителя Отдела Продаж</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

    <!-- Навигационное меню -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">АРМ Руководителя</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#dashboard">Дашборд</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tasks">Задачи</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#reports">Отчеты</a>
                    </li>
                </ul>
                <p class="nav-item mx-3"><?= $time; ?></p>
                <a href="#" class="nav-link">
                    <img src="../<?=$_SESSION['user']['avatar'];?>" alt="Аватар" class="rounded-circle" style="width: 40px; height: 40px;">
                </a> 
                <a href="../vendor/logout.php" class="nav-link mx-3">
                    Выход
                </a>                              
            </div>
        </div>
    </nav>


    <div id = "dashboard" class="container py-4">
        <!-- Панель управления сменами -->
        <?php
            require_once("../vendor/working_status_chek.php");
        ?>
        <!-- Блок статистики -->
        <div class="info-box">
            <h3>Статистика</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Выручка сегодня</h5>
                            <p class="text-center display-6"><?=$income_today . " ₽ ";?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Выручка за месяц</h5>
                            <p class="card-text text-center display-6"><?=$income_this_month . " ₽ ";?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <a href="plan_management.php" class="text-decoration-none link-dark">
                            <div class="card-body">
                                <h5 class="card-title">План на месяц</h5>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?=$percentage_completed?>%;" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"><?=round($percentage_completed,2)?>%</div>
                                </div>
                                <p class="mt-2"><?= $income_this_month . " ₽ из " . $monthly_plan . " ₽ " ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Количество клиентов сегодня</h5>
                            <p class="card-text text-center display-6"><?=$count_clients_today?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Количество успешных сделок сегодня</h5>
                            <p class="card-text text-center display-6"><?=$count_deals_today?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Клиенты на стадии переговоров</h5>
                            <p class="card-text text-center display-6"><?=$count_conversations?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Список сотрудников -->
        <h4>Сотрудники</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                <?php   
                    $status = ["working" => "Работает", 'absent' => 'Отсутсвует'];
                    while ($data = mysqli_fetch_assoc($all_managers_data)) {
                        ?>

                    <tr>
                        <td><?=htmlspecialchars($data['full_name'])?></td>
                        <td><?=htmlspecialchars($status[$data['status']])?></td>
                        <td>
                            <a href="add_task.php?manager_id=<?=htmlspecialchars($data["id"])?>">
                                <button class="btn btn-info btn-sm">Создать задачу</button>
                            </a>
                        </td>
                    </tr>
                <?php
                    }
                ?>
                </tbody>
            </table>
        </div>

        <!-- Управление задачами -->
        <div id="tasks" class="tasks">
            <h4>Задачи</h4>
            <a href="add_task.php">
                <button class="btn btn-success mb-3">Добавить задачу</button>
            </a>
            <div class="list-group">
                <?php
                    require_once("tasks_info.php");
                ?>
        </div>

        <!-- База клиентов и сделок -->

        <h4 class="mt-3">База клиентов и сделок</h4>
        <?php
            require_once ("clients_info.php");
        ?>
        <div class="mt-3">
            <a href="clients.php">
                <button class="btn btn-success mb-3">Все клиенты</button>
            </a>
        </div>
        <!-- Функции отчетности -->
        <div id="reports" class="mt-4">
            <h4>Отчеты</h4>

            <div class="list-group">
                <a href="report_day.php" class="list-group-item list-group-item-action">
                    Отчет за день
                </a>
                <a href="report_month.php" class="list-group-item list-group-item-action">
                    Отчет за месяц
                </a>
            </div>
            <div class="list-group">
                <a href="report_time_log.php" class="list-group-item list-group-item-action">
                    Отчет по посещению за месяц
                </a>
            </div>
        </div>
    </div>


    <script>
        $("#message").delay(1000).slideUp(2000, function() { $(this).remove(); });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
