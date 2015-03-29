<?php
require_once('functions.php');
$id = mysql_real_escape_string($_POST['id']);
$type = $_POST['type'];
if (! getuser()) die("You must be logged in to view minutes.");

if (isset($type))
{
	if ($type == "name")
	{
		$res = mysql_fetch_array(mysql_query("select `name` from `minutes` where `id` = '$id'"));
		echo $res[0];
	}
	else die("Unknown type");
	exit(0);
}
$query = "select count(`public`) as `n` from `minutes` where `id` = '$id'";
$result = mysql_fetch_array(mysql_query($query));
if ($result['n'] == 0) die("The minutes you requested do not exist.");
else if ($result['n'] > 1) die("Ambiguous request.");
if (isOfficer(getuser()) && ! isset($_POST['public'])) $query = "select `private` from `minutes` where `id` = '$id'";
else  $query = "select `public` from `minutes` where `id` = '$id'";
$result = mysql_fetch_array(mysql_query($query));
echo $result[0];
?>
