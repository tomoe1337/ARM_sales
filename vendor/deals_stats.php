<?php
	require_once("connect.php");



	function deals_data($only_mine = Null)
	{
		if (is_null($only_mine)){
			$date_this_month = date("Y-m-01");

			$sql = "SELECT sum(monthly_plan) FROM users";

			$data = mysqli_query($connect,$sql)->fetch_assoc();

			$monthly_plan = number_format($data['sum(monthly_plan)']);

			$sql = "SELECT * FROM users WHERE rights = 'manager'";

			$all_managers_data = mysqli_query($connect,$sql);

			$response = ["monthly_plan" => $monthly_plan]		
		}
	}





