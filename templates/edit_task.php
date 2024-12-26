<?php
    session_start();
    require_once("../vendor/tasks_info.php");
    require_once("../vendor/plan_stats.php");

    if (!isset($_GET['task_id'])){
        header('Location: /');
        die();
    }else{
        $task_id = htmlspecialchars($_GET['task_id']);
    }

    $plan_data = plan_data();

    $all_managers_data = $plan_data['all_managers_data'];
    $task_data = mysqli_fetch_assoc(task_data(id : False, cur_task : $task_id));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать задачу</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <a href="/">
            <button class="btn btn-secondary btn-sm">Назад</button>
        </a>
        <h1>Редактировать задачу</h1>

        <!-- Форма для редактирования задачи -->
        <div class="info-box">
            <form action="../vendor/edit_task.php" method="POST">
                <div class="mb-3">
                    <label for="taskId" class="form-label">id задачи</label>
                    <input name = "task_id" class="form-control" id="taskId" readonly value="<?=$task_data['id']?>">
                </div>
                <div class="mb-3">
                    <label for="taskEmployee" class="form-label">Выберите сотрудника</label>
                    <select name = "manager_id" class="form-control" id="taskEmployee" required>
                        <option value="">Выберите сотрудника</option>

                    <?php   
                        while ($data = mysqli_fetch_assoc($all_managers_data)) {?>
                            <option <?php if ($data['id'] == $task_data['manager_id']) echo 'selected="selected"';?> 
                            value="<?=htmlspecialchars($data['id'])?>">

                            <?=$data['full_name']?>
                                
                            </option>

                    <?php
                        }
                    ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="taskDescription" class="form-label">Описание задачи</label>
                    <textarea name = "task" class="form-control" id="taskDescription" rows="3" placeholder="Введите описание задачи"required><?=$task_data['task']?></textarea>
                </div>
                <div class="mb-3">
                    <label for="taskPriority" class="form-label">Приоритет</label>
                    <select name = "priority" class="form-control" id="taskPriority" required>
                        <option value="">Выберите приоритет</option>
                        <?php 
                            $dict = [1 => 'Низкий', 2 => 'Средний', 3 => 'Высокий'];

                            for($i = 1; $i < 4;$i++){?>
                                <option <?php if ($i == $task_data['priority']) echo 'selected="selected"';?> value="<?=$i;?>">
                                    <?=$dict[$i];?>
                                </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="taskDeadline" class="form-label">Срок выполнения</label>
                    <input name = "deadline" type="datetime-local" class="form-control" id="taskDeadline" required value="<?=$task_data['deadline']?>">
                </div>
                <div class="mb-3">
                    <label for="taskComment" class="form-label">Комментарий</label>
                    <textarea name = "comments" class="form-control" id="taskComment" rows="3" placeholder="Введите дополнительную информацию"><?=$task_data['comments']?></textarea>
                </div>
                <?php
                    if ($_SESSION["user"]["rights"] == "head"){
                ?>
                        <button type="submit" class="btn btn-success">Редактировать</button>
                <?php                
                    } else{?>
                        <button disabled class="btn btn-secondary">Нет доступа</button>
                <?php
                    }
                ?>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>