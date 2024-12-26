<div id="tasks" class="tasks">
    <div class="list-group">

<?php
    require_once ("../vendor/tasks_info.php");
    $date_now = date("Y-m-d H:i:s");
    $status_dict = ["progressing" => "Выполняется", "done" => "Завершено"];


    $task_data = task_data($id = ($_SESSION['user']['rights'] == "head")? Null : $_SESSION['user']['id']);
    while ($data = mysqli_fetch_assoc($task_data)){
?>
    <div  class="list-group-item">
        <a href="edit_task.php?task_id=<?=$data['id']?>" class="list-group-item-action text-decoration-none">
            <?=$data['id'] . ". " . $data['task'];?> - 
            <?php
                if ($data["deadline"] < $date_now){
                    echo "Просрочена";
                }else{
                    echo $status_dict[$data['status']];
                }
            ?>
        </a>
        <?php
            if (($data["status"]=="progressing")&&($_SESSION['user']['rights']=='manager')){
        ?>
                <a href="../vendor/compleate_task.php?task_id=<?=$data['id']?>">
                    <button class="btn btn-success mx-5">Выполненно</button>
                </a>
        <?php
            }else if (($data["status"]=="done")&&($_SESSION['user']['rights']=='head')){
        ?>
                <a href="../vendor/compleate_task.php?close=true&task_id=<?=$data['id']?>">
                    <button class="btn btn-success mx-5">Подтвердить</button>
                </a>
        <?php
            }
        ?>
    </div>
<?php
    }
?>
</div>
