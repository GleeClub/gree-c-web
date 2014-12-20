<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];

if (! isset($userEmail) || ! isOfficer($userEmail)) die("ACCESS_DENIED");

$action = $_POST['action'];
if ($action == 'gigcheck')
{
	$sql = "update `variables` set `gigCheck` = ";
	if ($_POST['value'] == '0') $sql .= '0';
	else $sql .= '1';
	if (! mysql_query($sql)) die(mysql_error());
	echo "OK";
}
else if ($action == 'gigreq')
{
	$num = mysql_real_escape_string($_POST['value']);
	if (! isset($num)) die("MISSING_PARAM");
	if (! mysql_query("update `variables` set `gigRequirement` = '$num'")) die(mysql_error());
	echo "OK";
}
else die("BAD_ACTION");
?>

