<?php
	require_once("connect.php");

	$date = date("Y-m-d");

	$sql = "SELECT d.id, d.amount, d.product, d.date, d.comments, d.manager_id, c.full_name, c.email 
	FROM deals d LEFT JOIN clients c ON d.client_id = c.id WHERE '$date'";
	$month_deals = mysqli_query($connect,$sql);

	$sql = "SELECT d.id, d.amount, d.product, d.date, d.comments, d.manager_id, c.full_name, c.email 
	FROM deals d LEFT JOIN clients c ON d.client_id = c.id WHERE d.date = '$date' ";
	$day_deals = mysqli_query($connect,$sql);

?>