<?php

	function plan_data($only_mine = Null)
	{	
		require("connect.php");

		if (is_null($only_mine)){
			$sql_today = "SELECT sum(daily_plan) FROM users";
			$sql_month = "SELECT sum(monthly_plan) FROM users";			
		}else{
			$sql_today = "SELECT sum(daily_plan) FROM users WHERE id = $only_mine";
			$sql_month = "SELECT sum(monthly_plan) FROM users WHERE id = $only_mine";

		}

		$data_today = mysqli_query($connect,$sql_today)->fetch_assoc();

		$daily_plan = number_format($data_today['sum(daily_plan)']);


		$data_month = mysqli_query($connect,$sql_month)->fetch_assoc();

		$monthly_plan = number_format($data_month['sum(monthly_plan)']);


		$sql_all = "SELECT * FROM users WHERE rights = 'manager'";

		$all_managers_data = mysqli_query($connect,$sql_all);


		$response = ['all_managers_data'=> $all_managers_data,
		'daily_plan' => $daily_plan,
		'monthly_plan' => $monthly_plan];
		
		$connect->close();
		return $response;

	}







