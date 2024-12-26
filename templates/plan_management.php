<?php
    require_once('../vendor/plan_stats.php');

    $plan_data = plan_data();

    $all_managers_data = $plan_data['all_managers_data'];
    $monthly_plan = htmlspecialchars($plan_data['monthly_plan']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление планом</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1>Управление планом</h1>

        <!-- Список менеджеров и управление планами -->
        <div class="info-box">
            <h3>Список менеджеров</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Имя менеджера</th>
                            <th>План на месяц (₽)</th>
                            <th>План на сегодня (₽)</th>
                        </tr>
                    </thead>
                    <tbody>
               <?php 
                    $daily_plan = 0;
                    while ($data = mysqli_fetch_assoc($all_managers_data)) {?>
                        <tr>
                            <form action = "../vendor/edit_plan.php" method="POST">
                                <td>
                                    <input type="text" name = "id" value="<?=htmlspecialchars($data["id"])?>" readonly style = "all:unset;max-width: 30px;">
                                </td>
                                <td><?=htmlspecialchars($data["full_name"])?></td>
                                <td>
                                    <input name = "monthly_plan" type="number" class="form-control" value="<?=$data["monthly_plan"]?>">
                                </td>
                                <td>
                                    <input name = "daily_plan" type="number" class="form-control" value="<?=htmlspecialchars($data["daily_plan"])?>"></td>
                                <td><button type = "submit" class="btn btn-success">Сохранить</button></td>
                            </form>
                        </tr>
                <?php
                    $daily_plan +=$data["daily_plan"];
                    }
                ?>      
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Общий план отдела -->
        <div class="info-box mt-4">
            <h3>Общий план отдела</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">План на месяц</h5>
                            <p class="card-text"><?=$monthly_plan ." ₽ "?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">План на сегодня</h5>
                            <p class="card-text"><?= number_format($daily_plan) . " ₽ " ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
