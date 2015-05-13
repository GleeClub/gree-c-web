<?php
require_once('functions.php');

$minutesLate = $_POST['minutesLate'];
$id = $_POST['id'];
$id = explode("_", $id);
$person = $id[1];
$event = $id[2];

$sql = "UPDATE attends SET minutesLate=$minutesLate WHERE memberID='$person' AND eventNo=$event;";
//echo $sql;
$result = mysql_query($sql);
//echo $result;
?>
