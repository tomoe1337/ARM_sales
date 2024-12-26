<?php
	session_start();
	require_once("connect.php");

	$amount = $_POST["amount"];
	$client_id = $_POST["client_id"];
	$date = $_POST["date"];
	$product = $_POST["product"];
	$comments = ($_POST["comments"] !=="") ? $_POST["comments"] : NULL;
	$manager_id = $_SESSION['user']['id'];

	$stmt = $connect -> prepare("INSERT INTO deals (amount, client_id , date, product, comments, manager_id)
		VALUES (?,?,?,?,?,?)");

	$stmt->bind_param("iisssi", $amount, $client_id , $date, $product, $comments, $manager_id);

	$stmt->execute();


	$stmt->close();
	$connect->close();

	header("Location: ../templates/deals.php");
