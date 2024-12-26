<?php
	require_once("connect.php");
	
	$data = mysqli_query($connect,"SELECT * FROM clients");
	$json = array();
	while ($item = mysqli_fetch_assoc($data)){
		$json[] = $item;
	}
	$json = json_encode($json,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);


$file = tmpfile();
fwrite($file, $json);

$metadata = stream_get_meta_data($file);

$tmp_directory = dirname($metadata['uri']) . '/clients_exported.json';

rename($metadata['uri'],$tmp_directory);
/*
 $metadata['uri'] = $metadata['uri'] . ".json";
 if (function_exists('curl_file_create')) {
   $cFile = curl_file_create($metadata['uri']);
 } else {
   $cFile = '@' . realpath($metadata['uri']);
 }
 
*/

	$url = "http://localhost:8888/vendor/worker.php";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ['export_file' => new CURLFile ($tmp_directory,'application/json',)]);
	$res = curl_exec($ch);
	curl_close($ch);

	echo($res);
