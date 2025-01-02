<?php
	require_once("connect.php");

	$date = date("Y-m-01");

	$sql = "SELECT t.date, u.full_name, t.arrived, t.left 
	FROM time_log t 
	LEFT JOIN users u on t.manager_id = u.id
	WHERE date >= '$date'";

	$month_time_log= mysqli_query($connect,$sql);

