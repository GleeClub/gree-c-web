<?php
require_once('functions.php');
$userEmail = getuser();
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