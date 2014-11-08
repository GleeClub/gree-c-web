<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

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