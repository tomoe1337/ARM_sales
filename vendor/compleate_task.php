<?php
	session_start();
	require_once('connect.php');

	$task_id = $_GET['task_id'];
	if (!isset($_GET['close'])){
		$stmt = $connect->prepare("UPDATE tasks SET status = 'done' WHERE id = ?;");
		$stmt->bind_param('i',$task_id);
		$stmt->execute();
		$response = $stmt->get_result();
		$stmt->close();

		header ('Location: /');
		die();
	}else if($_SESSION['user']["rights"] == "head"){
		$stmt = $connect->prepare("UPDATE tasks SET status = 'closed' WHERE id = ?;");
		$stmt->bind_param('i',$task_id);
		$stmt->execute();
		$response = $stmt->get_result();
		$stmt->close();

		header ('Location: /');
	}