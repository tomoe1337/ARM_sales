<?php
	require_once "connect.php";

	$topic = $_GET['topic'];
	$name = $_GET['name'];
	$number = $_GET['tel'];
	$email = $_GET['email'];
	$text = $_GET['text'];
	$pref_ans = $_GET['radio'] ?? NULL;


	$date = date("Y-m-d H:i:s");
	if (mysqli_query($connect, "INSERT INTO `feedback` (`id`, `topic`, `name`,`number`, `email`,`text`, `prefer_ans`,`date`) VALUES (NULL, '$topic','$name','$number','$email','$text','$pref_ans','$date')")){
		header ("Location: ../templates/os_form.php?success=true");
	}
	else{
		echo "error";
	}

