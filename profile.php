<?php 
	session_start();
if (!isset($_SESSION['user'])){
	header("Location: /main_page.php");
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
	
	<div style="font-size:30px">
		<img src = "<?= $_SESSION['user']['avatar']?>" width= "300" alt = "">
		<h2><?= $_SESSION['user']['full_name']?></h2>
		<a href="#"><?= $_SESSION['user']['email']?></a>
		<div style="display:flex;flex-direction: column;font-size: 25px;">
			<a style = "color: black;" href="templates/students_list.php">Запуск</a>
			<a style = "color: black;" href="templates/classrooms_list.php">Список аудиторий</a>
			<a style = "color: black;" href="export.php">Экспорт</a>
			<a style = "color: black;" href="templates/text.php">Форматирование текста</a>
			<a style = "color: black;" href="templates/os_form.php">Форма обрaтной связи</a>
			

			<a href="vendor/logout.php" class = "logout">Выход</a>
		</div>		
	</div>
</body>
</html>