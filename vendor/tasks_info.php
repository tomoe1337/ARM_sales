<?php
	function task_data($id = Null, $cur_task = Null)
	{	
		require('connect.php');
		if (is_null($id)){
			$sql = "SELECT * FROM tasks WHERE status <> 'closed'";

			$response = mysqli_query($connect,$sql);			
		}else if (is_null($cur_task)){
			$sql = "SELECT * FROM tasks WHERE manager_id = ? and status <> 'closed'";
			$stmt = $connect->prepare($sql);
			$stmt->bind_param('i',$id);
			$stmt->execute();
			$response = $stmt->get_result();
			$stmt->close();
		} else{
			$sql = "SELECT * FROM tasks WHERE id = ?";
			$stmt = $connect->prepare($sql);
			$stmt->bind_param('i',$cur_task );
			$stmt->execute();
			$response = $stmt->get_result();
			$stmt->close();			

		}
		$connect -> close();
		return $response;
	}