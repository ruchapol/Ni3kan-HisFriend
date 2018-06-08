<?php

// date format <YYYY/MM/DD>
function getWeatherHTML($date){
  global $cookie;
  $date = preg_replace("/\//", "", $date);
  $url = "https://www.timeanddate.com/scripts/cityajax.php?n=thailand/bangkok&mode=historic&" .
         "hd=" . $date . "&month=[object%20HTMLSelectElement]&json=1";
  //echo $url."\n";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: $cookie"));
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

function praseBrokenJson($brokenJson){
  $brokenJson = preg_replace("/c\:/",'"c":',$brokenJson);
  $brokenJson = preg_replace("/s\:/",'"s":',$brokenJson);
  $brokenJson = preg_replace("/h\:/",'"h":',$brokenJson);
  $brokenJson = preg_replace("/\&nbsp\;/",' ',$brokenJson);

  //echo $a . "\n";
  $json = json_decode($brokenJson, true);

  return $json;

}

function dateRange( $first, $last, $step = '+1 day', $format = 'Y/m/d' ) {

	$dates = array();
	$current = strtotime( $first );
	$last = strtotime( $last );

	while( $current <= $last ) {

		$dates[] = date( $format, $current );
		$current = strtotime( $step, $current );
	}

	return $dates;
}

// date format <YYYY/MM/DD>, Creat temp file
function creatJsonFile($date, $weatherJson) {

  $date = preg_replace("/\//", "", $date);
  if (!file_exists('./weather')) {
    mkdir('./weather', 0777, true);
  }
  $file = fopen("./weather/weatherJson" . $date . ".txt", "w");
  fwrite($file, $weatherJson."\n");
  fclose($file);

}

// date for mate <YYYY/MM/DD 00:00:00>
function readJsonFile($date) {

  $date = preg_replace("/\//", "", $date);
  $jsonPathFile = "./weather/weatherJson" . $date . ".txt";

  $file = fopen($jsonPathFile, "r");
  $jsonText = fread($file, filesize($jsonPathFile));
  fclose($file);

  return json_decode($jsonText, true);

}

// input json object, date as <YYYY/MM/DD>
function logFormater($weatherJson, $date) {

  $fullTextStr = "";

  foreach($weatherJson as $aWeatherLog){
    $dateTime = $date . " " . substr($aWeatherLog["c"][0]["h"], 0, 5) . ":00";
    $temperature = $aWeatherLog["c"][2]["h"];
    $weather = $aWeatherLog["c"][3]["h"];
    $windSpeed = $aWeatherLog["c"][4]["h"];
    preg_match('/(title=")(.*)("\>)/', $aWeatherLog["c"][5]["h"],$matchs);
    $windDirection = $matchs[2];
    $humidity = $aWeatherLog["c"][6]["h"];
    $pressure = $aWeatherLog["c"][7]["h"];
    $visibility = $aWeatherLog["c"][8]["h"];

    $textALog = "time : " . $dateTime . " temp : " . $temperature . " weather : " .
                $weather . " wind : " . $windSpeed . " wind direction : " .
                $windDirection . " humidity : " . $humidity . " visibility : " .
                $visibility . "\n";

    $fullTextStr .= $textALog;

  }
  return $fullTextStr;
}

//$theDate = "2018/05/23";

$dateArray = dateRange("2018/05/23", date('Y/m/d'));
$logStr = "";

$weatherJson = readJsonFile($theDate);

//var_dump($weatherJson);

// foreach ($weatherJson as $aWeather) {
//   var_dump($aWeather["c"]);
// }

//echo logFormater($weatherJson, $theDate);

foreach ($dateArray as $aDate) {

  $weatherJson = readJsonFile($aDate);
  $logStr .= logFormater($weatherJson, $aDate);

  //$json = praseBrokenJson($brokenJson);

  //var_dump($weatherJson);
  // write file
  //creatJsonFile($aDate, json_encode($json));
}

//
//
if (!file_exists('./weather')) {
   mkdir('./weather', 0777, true);
}

$file = fopen("./weather/weatherLog.txt", "w");
fwrite($file, $logStr."\n");
fclose($file);

?>
