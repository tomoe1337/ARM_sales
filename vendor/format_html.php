<?php

function formatDashHtmlText($html) {
  // Замена тире с пробелами на среднее тире
  $html = preg_replace('/\s+-\s+/', ' &ndash; ', $html);

  // Замена двойного тире с пробелами на длинное тире
  $html = preg_replace('/\s+-{2}\s+/', ' &mdash; ', $html);

  // Привязка длинного тире к предыдущему слову
  $html = preg_replace('/\s+(?<!\s)&mdash;\s+/', '&nbsp; ', $html);

  return $html;
}


function formatCutHtmlText($html) {

	$html = preg_replace('/\s?(итд|и тд)\s/ui', ' и т.д.',$html);

	$html = preg_replace('/\s?(итп|и тп)\s?/ui', ' и т.п.',$html);

	return $html;
}


function filterWords($html){

	$word_list = ['пух','рот','ехать', 'около', 'для'];

	foreach ($word_list as $word) {

		$replace = str_repeat('#',mb_strlen($word));

		$html = preg_replace("/\b$word/ui", "$replace", $html);

	}

	return $html;
}
function tableIndicator($html){
	$html = $BOM.$html;

	$result = "";

	$dom = new DOMDocument();
	@$dom->loadHTML($html);

	$tables = $dom->getElementsByTagName('table');

	for ($i = 0; $i < $tables->length; $i++) {
		$table = $tables->item($i);

	  $firstCell = $table->getElementsByTagName('tr')->item(0);
	  $result = $result . "Таблица №". $i+1 . " " . $firstCell->nodeValue . "<br>";
	}

	return $result;


}


if (!empty($_POST['parser'])){

	if ($_POST['textarea']){

		$formattedDash = formatDashHtmlText($_POST['textarea']);

		$formattedCatback = formatCutHtmlText($formattedDash);

		$filteredWords = filterWords($formattedCatback);
		//echo 'Задание 5. Форматирование тире: <br>' . $formattedDash . '<br>';
		//echo 'Задание 8. Растановка точек в сокращениях: <br>' . $formattedCatback . '<br>';
		$tableIndicator = tableIndicator($filteredWords);
		echo "Указатель таблиц: <br>";
		if ($tableIndicator) echo ($tableIndicator);
		else echo "Таблиц не найдено <br>";


		echo ("<br>" . ($filteredWords));

	}
}
