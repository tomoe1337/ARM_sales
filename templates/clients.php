<?php
    require_once('../vendor/clients_stats.php');
    $page = (isset($_GET['page'])) ? htmlspecialchars($_GET['page']) : 1;
    if ($page < 1) $page = 1;

    $count_clients = get_clients_info()->num_rows;
    $clients_list = get_clients_info(page : $page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить клиента</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <a href="/">
            <button type="submit" class="btn btn-secondary btn-sm">Назад</button>
        </a>
           

        <h1>Клиенты</h1>
        <!-- Добавление формы при необходимости-->
        <?php
            if (isset($_GET['create'])){
                if (filter_var($_GET['create'], FILTER_VALIDATE_BOOLEAN)){
                    require_once ("add_client.php");
                }
            }
        ?>

        <!-- Список клиентов -->
        <?php if (!isset($_GET['create'])):?>
            <div class="mt-3">
                <a href="clients.php/?create=true">
                    <button class="btn btn-success mb-3">Добавить</button>
                </a>
            </div>
        <?php endif;?>
        <div class="info-box">
            <h3>Список клиентов</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Имя</th>
                            <th>Контактный номер</th>
                            <th>Email</th>
                            <th>Дата последнего контакта</th>
                            <th>Статус сделки</th>
                            <th>Комментарий</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php

                        while ($data = mysqli_fetch_assoc($clients_list)){
                    ?>
                        <tr>
                            <td><?=htmlspecialchars($data["id"])?></td>
                            <td><?=htmlspecialchars($data["full_name"])?></td>
                            <td><?=htmlspecialchars($data["phone"])?></td>
                            <td><?=htmlspecialchars($data["email"])?></td>
                            <td><?=htmlspecialchars($data["last_contact_date"])?></td>
                            <td><?=htmlspecialchars($data["status"])?></td>
                            <td><?=htmlspecialchars($data["comments"])?></td>
                        </tr>
                    <?php
                        }
                    ?>

                    </tbody>
                </table>
            </div>
        </div>
        <!-- Кнопка импорта -->
        <div class="d-flex mb-3">
            <a href="../vendor/export_script.php">
                <button class="btn btn-secondary">Экспортировать клиентов</button>
            </a>
        </div>

        <!-- Навигация по страницам -->
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <li class="page-item <?php if ($page == 1) echo "disabled";?>">
              <a class="page-link" href="?page=<?=$page-1;?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>

            <?php for ($i = 1; $i < intdiv($count_clients, 10)+1; $i++) {
            ?>
                <li class="page-item <?php if ($i==$page) echo "active";?>">
                    <a class="page-link" href="?page=<?=$i;?>"><?=$i;?></a>
                </li>

            <?php
                }
            ?>

            <li class="page-item <?php if ($page == intdiv($count_clients, 10)) echo "disabled";?>">
              <a class="page-link" href="?page=<?=$page+1;?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
