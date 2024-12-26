<?php

	function deals_data($only_mine = Null)
	{	
		require("connect.php");

		$date_this_month = date("Y-m-01");
		$date_today = date("Y-m-d");

		if (is_null($only_mine)){
			$sql_today = "SELECT COALESCE(sum(amount),0),count(*)
			FROM deals
			WHERE date = '$date_today'";

			$sql_month = "SELECT COALESCE(sum(amount),0)
			FROM deals
			WHERE  date >= '$date_this_month'";
				
		}else{
			$sql_today = "SELECT COALESCE(sum(amount),0),count(*)
			FROM deals
			WHERE date = '$date_today' AND manager_id = '$only_mine'";

			$sql_month = "SELECT COALESCE(sum(amount),0)
			FROM deals
			WHERE  date >= '$date_this_month' AND manager_id = '$only_mine'";			

		}

		$data_today = mysqli_query($connect,$sql_today)->fetch_assoc();

		$income_today = number_format($data_today['COALESCE(sum(amount),0)']);
		$count_deals_today = ($data_today["count(*)"]);

		$data_month = mysqli_query($connect,$sql_month)->fetch_assoc();
		$income_this_month  = number_format($data_month["COALESCE(sum(amount),0)"]);

		$response = ['income_today'=> $income_today,
		'income_this_month' => $income_this_month,
		'count_deals_today' => $count_deals_today];

		$connect->close();
		return $response;

	}







