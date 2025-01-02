<?php
	require_once("connect.php");

	$task_id = $_POST["task_id"];
	$manager_id = $_POST["manager_id"];
	$task = $_POST["task"];
	$priority = $_POST["priority"];
	$deadline = $_POST["deadline"];
	$comments = ($_POST["comments"] !=="") ? $_POST["comments"] : NULL;


//Если была нажада кнопка удалить и подтвердить, то удаляем запись. Иначе редактируем
	if (isset($_POST['modal_confirm'])){
		$stmt = $connect -> prepare("DELETE FROM tasks WHERE `tasks`.`id` = ?");

		$stmt->bind_param("i", $task_id);
	} else {
		$stmt = $connect -> prepare("UPDATE tasks SET manager_id = ?, task = ?, priority = ?, deadline = ?,comments = ? 
			WHERE id = ?");

		$stmt->bind_param("isissi", $manager_id, $task, $priority,$deadline,$comments, $task_id);
		$stmt->execute();		
	}

	$stmt->execute();		
	$stmt->close();
	$connect->close();

	header("Location: /index.php");