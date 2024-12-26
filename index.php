<?php 
	session_start();

if (isset($_SESSION['user'])){
	if(in_array($_SESSION['user']['rights'] , ["manager","head"])){

		header("Location: /templates/" . $_SESSION['user']['rights'] ."_page.php");

	}
} 
?>

<!doctype html>
<html lang = "en">
<head>
	<meta charset="utf-8">
	<title>Авторизация и регистрация</title>
	<link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
	
	<form action="vendor/signin.php" method="post">
		<label>Логин</label>
		<input type = "text", name='login' placeholder="Введите свой логин">
		<label>Пароль</label>
		<input type="password", name="password" placeholder="Введите пароль">
		<button>Войти</button>
		<p>
			У вас нет аккаунта? - <a href="/register.php">зарегистрируйтесь</a>
		</p>
		<?php
			if (isset($_SESSION['message'])) {
				echo '<p class = "msg">' . $_SESSION['message'] . '</p>';
			}
			unset($_SESSION['message']);	
		?>
	</form>
</body>
</html>