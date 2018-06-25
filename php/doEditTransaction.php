<?php
require_once('./functions.php');

if (! hasPermission("edit-transaction")) die("DENIED");

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
