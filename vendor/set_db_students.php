<?php
session_start();
	require_once "connect.php";
	$student_id = $_GET['student_id'];
	if (isset($_POST['delete-button'])){
		mysqli_query($connect,"DELETE FROM `tasks` WHERE `student_id` = $student_id");

	}
	elseif (isset($_POST['new-task'])) {
		$date =date('Y-m-d H:i:s');
		$task = $_POST['task'];
		echo $sql ="INSERT INTO `tasks` (`student_id`, `task`, `date`) VALUES ('$student_id','$task','$date')";
		mysqli_query($connect,$sql);

	}

header("Location: ../templates/student.php?student_id={$student_id}");

