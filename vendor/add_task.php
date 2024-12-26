<?php
	require_once("connect.php");

	$manager_id = $_POST["manager_id"];
	$task = $_POST["task"];
	$taskPriority = $_POST["taskPriority"];
	$taskDeadline = $_POST["taskDeadline"];
	$taskComment = ($_POST["taskComment"] !=="") ? $_POST["taskComment"] : NULL;

	$stmt = $connect -> prepare("INSERT INTO tasks (manager_id, task , priority, deadline, comments)
		VALUES (?,?,?,?,?)");

	$stmt->bind_param("issss", $manager_id, $task, $taskPriority, $taskDeadline, $taskComment);

	$stmt->execute();


	$stmt->close();
	$connect->close();

	header("Location: ../templates/add_task.php");
