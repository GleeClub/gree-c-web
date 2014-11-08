<?php
require_once('./functions.php');
$userEmail = $_COOKIE['email'];
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

if (! isOfficer($userEmail)) // TODO President or Treasurer
{
	echo "DENIED";
	exit(1);
}

$id = mysql_real_escape_string($_POST['id']);
$action = $_POST['action'];
if (! isset($id))
{
	echo "NO_ID";
	exit(1);
}
if ($action == 'remove')
{
	$sql = "delete from `transaction` where `transactionID` = '$id'";
	if (mysql_query($sql)) echo "OK";
	else echo "ERR";
}
else if ($action == 'resolve')
{
	$sql = "update `transaction` set `resolved` = '1' where `transactionID` = '$id'";
	if (mysql_query($sql)) echo "OK";
	else echo "ERR";
}
else if ($action == 'unresolve')
{
	$sql = "update `transaction` set `resolved` = '0'  where `transactionID` = '$id'";
	if (mysql_query($sql)) echo "OK";
	else echo "ERR";
}
else
{
	echo "HUH?";
}
?>
