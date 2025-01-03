<?php 
	session_start();
if (isset($_SESSION['user'])){
	header("Location: profile.php");
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
	
	<form action="vendor/signup.php" method="post" enctype="multipart/form-data">
		<label>ФИО</label>
		<input type = "text" name = "full_name" placeholder="Введите свое полное имя">
		<label>Логин</label>
		<input type = "text" name = "login" placeholder="Введите свой логин">
		<label>Почта</label>
		<input type="email" name = "email" placeholder="Введите ваш email">
		<label>Изображение профиля</label>
		<input type="file" name = "avatar">

        <label for="Employee" class="form-label">Выберите должность</label>
        <select class="form-control" name = "rights" id="Employee" required>
            <option value=""></option>
            <option value="manager">Менеджер</option>
            <option value="head">Руководитель</option>
        </select>

		<label>Пароль</label>
		<input type="password" name = "password" placeholder="Введите пароль">
		<label>Подтверждение пароля</label>
		<input type="password" name = "password_confirm" placeholder="Подтвердите пароль">
		<button>Зарегестрироваться</button>
		<p>
			У вас уже есть аккаунт? - <a href="/index.php">авторизируйтесь</a>
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