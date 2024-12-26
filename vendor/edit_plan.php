<?php
	require_once("connect.php");

	$manager_id = $_POST['id'];

	$monthly_plan = $_POST['monthly_plan'];

	$daily_plan = $_POST['daily_plan'];
	
	$stmt = $connect -> prepare("UPDATE users SET monthly_plan = ?, daily_plan = ? WHERE id = ?");

	$stmt->bind_param("iii", $monthly_plan, $daily_plan, $manager_id);

	$stmt->execute();


	$stmt->close();
	$connect->close();

	header("Location: ../templates/plan_management.php");