<?php

	function get_deals_info($count = Null, $page = Null)
	{	
		require("connect.php");
		if (!is_null($count)){
			$response = mysqli_query($connect,"SELECT * FROM deals ORDER BY id DESC LIMIT $count");
		}else
			$response = mysqli_query($connect,"SELECT * FROM deals");

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