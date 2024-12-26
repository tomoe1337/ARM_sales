<?php
    require_once("../vendor/income_stats.php");
    require_once("../vendor/plan_stats.php");
    require_once("../vendor/deal_stats.php");

    session_start();
    $working_status = $_SESSION["user"]["status"] === "working";
    $time = date('H:i',strtotime("now -3 GMT"));



    $stats = deals_data($_SESSION['user']['id']);
    $income_today = $stats['income_today'];
    $income_this_month = $stats['income_this_month'];
    $count_deals_today = $stats['count_deals_today'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>АРМ Сотрудника Отдела Продаж</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>    
    <!-- Навигационное меню -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">АРМ Отдела Продаж</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#task-list">Текущие задачи</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#search-client">Поиск клиента</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Статистика</a>
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

    <div class="container py-4">
        <!-- Верхняя часть -->
        <?php
            require_once("../vendor/working_status_chek.php");
        ?>
        <!-- Рабочая панель -->
        <div class = "row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Сумма продаж сегодня</h5>
                        <p class="text-center display-6"><?=$income_today?> ₽</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Сумма продаж в этом месяце</h5>
                        <p class="text-center display-6"><?=$income_this_month?> ₽</p>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <!-- Список текущих задач -->
            <div class="col-lg-6 mb-4">
                <h4>Текущие задачи</h4>
                <div id="task-list">

                    <?php
                        require_once("tasks_info.php");
                    ?>

            </div>
            <div class="col-lg-6 mb-4">
                <h4>Клиенты</h4>
                <?php
                    require_once ("clients_info.php");
                ?>
                <div class="mt-3">
                    <a href="clients.php">
                        <button class="btn btn-success mb-3">Все клиенты</button>
                    </a>
                </div>
            </div>

        </div>

        <!-- Блок статистики и кнопки функционала -->
        <div class="row">
            <!-- Кнопки функционала -->
            <div class="col-lg-4 mb-4"></div>
            <div class="col-lg-4 mb-4">
                <h4>Функционал</h4>
                <div class="d-grid gap-2">
                    <a href="clients.php">
                        <button class="btn btn-success">Добавить клиента</button>
                    </a>
                    <a href="deals.php">
                        <button class="btn btn-info">Просмотр сделок</button>
                    </a>
                    <a href="work_report.php">
                        <button class="btn btn-warning">Отчет по смене</button>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 mb-4"></div>

        </div>

    </div>

    <script>
        $("#message").delay(1000).slideUp(2000, function() { $(this).remove(); });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
