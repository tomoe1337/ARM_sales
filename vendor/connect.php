<?php

	$connect = mysqli_connect(hostname: "localhost", username: "user_name",password:'', database:'ARM');

	if (!$connect){
		die("Error connect to database");
	}