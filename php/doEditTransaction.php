<?php
require_once('./functions.php');

if (! hasPermission("edit-transaction")) err("DENIED");

$id = $_POST['id'];
$action = $_POST['action'];
if (! isset($id))
	err("NO_ID");
if ($action == 'remove')
	query("delete from `transaction` where `transactionID` = ?", [$id]);
else if ($action == 'resolve')
	query("update `transaction` set `resolved` = '1' where `transactionID` = ?", [$id]);
else if ($action == 'unresolve')
	 query("update `transaction` set `resolved` = '0'  where `transactionID` = ?", [$id]);
else
	echo "HUH?";
?>
