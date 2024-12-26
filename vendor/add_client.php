<?php
	session_start();
	require_once("connect.php");

	$full_name = $_POST["full_name"];
	$phone = $_POST["phone"];
	$email = $_POST["email"];
	$last_contact_date = $_POST["last_contact_date"];
	$status = $_POST["status"];
	$comments = ($_POST["comments"] !=="") ? $_POST["comments"] : NULL;
	$manager_id = $_SESSION['user']['id'];

	$stmt = $connect -> prepare("INSERT INTO clients (full_name, phone , email, last_contact_date, status, comments, manager_id)
		VALUES (?,?,?,?,?,?,?)");

	$stmt->bind_param("ssssssi", $full_name, $phone, $email, $last_contact_date, $status, $comments,$manager_id);

	$stmt->execute();


	$stmt->close();
	$connect->close();

	header("Location: ../templates/add_client.php");
