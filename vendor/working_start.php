<?php 
	session_start();
	if (!(isset($_SESSION["user"]))){
		header("Location: /");
		die();
	}
	require_once ("connect.php");

	$user_id = $_SESSION['user']['id'];

	$sql = "UPDATE `users` SET `status` = 'working' WHERE `users`.`id` = '$user_id'";
	mysqli_query($connect,$sql);

	$date = date("Y-m-d");
	$time = date('H:i',strtotime("now -3 GMT"));

	$sql = "INSERT INTO `time_log`
	 (`id`, `manager_id`, `arrived`, `left`, `date`) 
	 VALUES (NULL, '$user_id', '$time', NULL, '$date')";

	mysqli_query($connect,$sql);
	
	$connect->close();


	$_SESSION['user']['status'] = "working";
	$_SESSION['message'] = "Приятной работы!";

	header("Location: /templates/" . $_SESSION['user']['rights'] ."_page.php");
