<?php

/*
	This script will generate false stats for days num and print out sql. It has been included for developers.

	Acceptable parameters: days -- how many days to create fake data for into the past. (?days=30 will create fake stats for 30 days back).
*/

/* Random Number Limits - (heh, not SO random then eh?) */
$min = 1000;
$max = 2000;

$numdays = $_GET['days'];

$sql = "SQL is as follows:<br>\n";

$sql .= "TRUNCATE `ep_archives`;<br>";

while($numdays)
{
	$currand = rand($min, $max);
	$currand2 = rand($min, $currand-500);
	$cur = time() - 60*60*24*$numdays;
	$sql .= "INSERT INTO ep_archives (date, hits, visits) VALUES ('".$cur."', '".$currand."', '".$currand2."');"."<br>\n";
	$totalhits += $currand;
	$totalvisits += $currand2;
	$numdays--;
}

$sql .= "INSERT INTO ep_archives (date, hits, visits) VALUES ('0', '".$totalhits."', '".$totalvisits."');"."<br>\n";

echo $sql;
