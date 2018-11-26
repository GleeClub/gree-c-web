<?php
require_once('functions.php');

if (! isset($USER) || ! hasPermission("edit-grading")) err("ACCESS_DENIED");

$action = $_POST['action'];
if ($action == 'gigcheck')
{
	query("update `variables` set `gigCheck` = ?", [$_POST['value'] == '0' ? 0 : 1]);
	echo "OK";
}
else if ($action == 'gigreq')
{
	$num = $_POST['value'];
	if (! isset($_POST["value"])) err("MISSING_PARAM");
	query("update `semester` set `gigreq` = ? where `semester` = ?", [$num, $SEMESTER]);
	echo "OK";
}
else err("BAD_ACTION");
?>

