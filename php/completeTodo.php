<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
$id = $_POST['id'];
$status = $_POST['status'];

if($status=='complete'){
	$sql = "UPDATE `todo` SET completed=1 WHERE id=$id;";
	mysql_query($sql);
}
if($status=='incomplete'){
	$sql = "UPDATE `todo` SET completed=0 WHERE id=$id;";
	mysql_query($sql);
}

?>