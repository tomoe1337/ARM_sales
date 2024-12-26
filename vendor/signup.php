<?php

	session_start();
	require_once "connect.php";

	$full_name = $_POST["full_name"];
	$login = $_POST["login"];
	$email = $_POST["email"];
	$password = $_POST["password"];
	$password_confirm = $_POST["password_confirm"];
	$rights = $_POST["rights"];

	if (strlen($password) < 8){
		$_SESSION['message'] = "Слабый пароль";
		header("Location: ../register.php");
		die();
	}

	$data_signup = array($full_name,$login,$email,$password);
	if (in_array('', $data_signup)){
		$_SESSION['message'] = "Заполните все поля";
		header("Location: ../register.php");
		die();
	}
	if($password === $password_confirm){
		//$_FILES['avatar']['name']
		$path = "uploads/" . time() .$_FILES['avatar']['name'];
		if (!move_uploaded_file($_FILES['avatar']['tmp_name'], "../" . $path)){
			$_SESSION["message"] = "Ошибка при загрузке изображения";
			header("Location: ../register.php");
		}
		$password = md5($password);

		mysqli_query($connect, "INSERT INTO `users` (`id`, `full_name`,`login`, `email`,`password`, `avatar`, `rights`) VALUES (NULL, '$full_name','$login','$email','$password','$path', '$rights')");
		$_SESSION["message"] = "Регистрация прошла успешно!";
		header("Location: ../index.php");

	} else{
		$_SESSION['message'] = "Пароли не совпадают";
		header("Location: ../register.php");
	}

