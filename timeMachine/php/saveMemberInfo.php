<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];

$value = $_POST["value"];
$person = $_POST["person"];
$attribute = $_POST["attribute"];
if(!strrpos($value, "<span")){
	$sql = "UPDATE `member` SET $attribute='$value' WHERE email='$person';";
	mysql_query($sql);
	//echo $sql;
	echo $value; //give the new value back for user feedback
}
else{
	//figure out how to get "4" from <span class="badge">4</span>m
}
?>
