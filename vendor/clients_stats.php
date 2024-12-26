<?php
	require("connect.php");

	$date_today = date("Y-m-d");

	$sql = "SELECT count(*)
	FROM clients
	WHERE last_contact_date = '$date_today'";

	$data = mysqli_query($connect,$sql)->fetch_assoc();

	$count_clients_today = $data["count(*)"];

	$sql = "SELECT count(*)
	FROM clients
	WHERE  status = 'Переговоры'";

	$data = mysqli_query($connect,$sql)->fetch_assoc();

	$count_conversations  = $data['count(*)'];



	function get_clients_info($count = Null, $page = Null)
	{	
		require("connect.php");
		if (!is_null($count)){
			$response = mysqli_query($connect,"SELECT * FROM clients ORDER BY id DESC LIMIT $count");
		}else
			$response = mysqli_query($connect,"SELECT * FROM clients");

		if (!is_null($page)){

			$count = $page * 10;
			$offset = ($count > 10) ? $count : 0;
			$sql = "SELECT * FROM clients ORDER BY id DESC LIMIT ? OFFSET ?";

			$stmt = $connect -> prepare($sql);
			$stmt->bind_param("ii",$count,$offset);

			$stmt->execute();
			$response = $stmt->get_result();

			$stmt->close();
		}

		$connect -> close();
		return $response;

	}

