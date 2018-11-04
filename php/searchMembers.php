<?php
//This returns the results for the To: field in creating a message.
require_once('functions.php');
$q = $_GET["q"] . "%";

$ret = "[\n";
$i = 1;
foreach (query("select `email`, `firstName`, `prefName`, `lastName` from `member` where `firstName` like ? or `prefName` like ? or `lastName` like ?", [$q, $q, $q], QALL) as $arr)
{
	$fn = $arr['firstName'];
	$pn = $arr['prefName'];
	$ln = $arr['lastName'];
	$tmp['id'] = $arr['email'];
	$tmp['name'] = $fn . " \"" . $pn . "\" " . $ln;
	$ret2[] = $tmp;
	//$ret .= "\t{\"id\":\"$i\",\"name\":\"$pn $ln\"},\n";
	$i++;
}

if(stripos("tenor 1s", $q) !== false) {
	$tmp['id'] = "tenor1s";
	$tmp['name'] = "Tenor 1s";
	$ret2[] = $tmp;
}

if(stripos("tenor 2s", $q) !== false) {
	$tmp['id'] = "tenor2s";
	$tmp['name'] = "Tenor 2s";
	$ret2[] = $tmp;
}

if(stripos("baritones", $q) !== false) {
	$tmp['id'] = "baritones";
	$tmp['name'] = "Baritones";
	$ret2[] = $tmp;
}

if(stripos("basses", $q) !== false) {
	$tmp['id'] = "basses";
	$tmp['name'] = "Basses";
	$ret2[] = $tmp;
}


$ret .= "]";
echo json_encode($ret2);
?>
