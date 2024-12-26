<?php

	function get_time_log($id){
		require("connect.php");

		$date_today = date("Y-m-d");
		$sql = "SELECT * FROM time_log WHERE `date` = '$date_today' AND manager_id = '$id'";

		$data = mysqli_query($connect,$sql)->fetch_assoc();
		return ['arrived' => $data['arrived'], 'left' => $data['left']];
		$connect->close();

	}
