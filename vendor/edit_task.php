<?php
	require_once("connect.php");

	$task_id = $_POST["task_id"];
	$manager_id = $_POST["manager_id"];
	$task = $_POST["task"];
	$priority = $_POST["priority"];
	$deadline = $_POST["deadline"];
	$comments = ($_POST["comments"] !=="") ? $_POST["comments"] : NULL;

	$stmt = $connect -> prepare("UPDATE tasks SET manager_id = ?, task = ?, priority = ?, deadline = ?,comments = ? 
		WHERE id = ?");

	$stmt->bind_param("isissi", $manager_id, $task, $priority,$deadline,$comments, $task_id);

	$stmt->execute();


	$stmt->close();
	$connect->close();

	header("Location: ../templates/edit_task.php?task_id=" . $task_id);