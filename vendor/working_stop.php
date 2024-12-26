<?php 
	session_start();
	if (!(isset($_SESSION["user"]))){
		header("Location: /");
		die();
	}
	require_once ("connect.php");

	$user_id = $_SESSION['user']['id'];

	$sql = "UPDATE `users` SET `status` = 'absent' WHERE `users`.`id` = '$user_id'";
	mysqli_query($connect,$sql);

	
	$date = date("Y-m-d");
	$time = date('H:i',strtotime("now -3 GMT"));

	$sql = "UPDATE `time_log` SET `left` = '$time' 
	WHERE `manager_id` = '$user_id' and date = '$date' ORDER BY id DESC LIMIT 1";
	print_r ($sql);
	mysqli_query($connect,$sql);

	$_SESSION['user']['status'] = "absent";

	$_SESSION['message'] = "Всего доброго!";
	header("Location: /templates/" . $_SESSION['user']['rights'] ."_page.php");
