<?php
require_once('connect.php');


$sql = "INSERT INTO classrooms (classrooms_id, img, building_id, eqp_list, сapacity) 
VALUES 
('2','https://avatars.mds.yandex.net/get-altay/2408158/2a00000171d075f117d26ea2ed11b7762207/XXL_height',2,'Проектор, интерактивная доска, сплит-система, лазерная указка, компьютеры','100'),
('3','https://dimmax.pro/uploads/gotovie-resheniya/Uchebnii_pomejeniya_02.jpg',2,'Проектор, интерактивная доска, микрофон, динамики','200') 
ON DUPLICATE KEY UPDATE classrooms_id = classrooms_id";



mysqli_query($connect,$sql);
echo("ok");