<?php
    require_once("../vendor/plan_stats.php");
    $plan_data = plan_data();

    $all_managers_data = $plan_data['all_managers_data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить задачу</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <a href="/">
            <button type="submit" class="btn btn-secondary btn-sm">Назад</button>
        </a>
           
        <h1>Добавить задачу</h1>

        <!-- Форма для добавления задачи -->
        <div class="info-box">
            <form action="../vendor/add_task.php" method="POST">
                <div class="mb-3">
                    <label for="taskEmployee" class="form-label">Выберите сотрудника</label>
                    <select name = "manager_id" class="form-control" id="taskEmployee" required>
                        <option value="">Выберите сотрудника</option>
                        <?php
                            while ($data = mysqli_fetch_assoc($all_managers_data)){                            
                        ?>
                            <option value="<?=$data['id']?>" 
                            <?php if(isset($_GET["manager_id"])){
                                if ($_GET["manager_id"] == $data['id']) echo 'selected="selected"';
                            }?>
                            >
                                <?=$data['full_name']?>
                            </option>
                        <?php
                            }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="taskDescription" class="form-label">Описание задачи</label>
                    <textarea name = "task" class="form-control" id="taskDescription" rows="3" placeholder="Введите описание задачи" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="taskPriority" class="form-label">Приоритет</label>
                    <select name = "taskPriority" class="form-control" id="taskPriority" required>
                        <option value="">Выберите приоритет</option>
                        <option value="1">Низкий</option>
                        <option value="2">Средний</option>
                        <option value="3">Высокий</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="taskDeadline" class="form-label">Срок выполнения</label>
                    <input name = "taskDeadline" type="datetime-local" class="form-control" id="taskDeadline" required>
                </div>
                <div class="mb-3">
                    <label for="taskComment" class="form-label">Комментарий</label>
                    <textarea name = "taskComment" class="form-control" id="taskComment" rows="3" placeholder="Введите дополнительную информацию"></textarea>
                </div>
                <button type="submit" class="btn btn-success">Создать задачу</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
