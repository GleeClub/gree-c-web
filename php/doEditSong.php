<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase") or die("cannot select DB");
$id = mysql_real_escape_string($_POST['id']);
$action = mysql_real_escape_string($_POST['action']);
$title = mysql_real_escape_string($_POST['name']);
$info = mysql_real_escape_string($_POST['desc']);
$current = mysql_real_escape_string($_POST['current']);
if (! isset($_COOKIE['email']) || ! isOfficer($_COOKIE['email']))
{
	echo "UNAUTHORIZED";
	exit(1);
}
if ($action == "add")
{
	$query = "insert into `song` (`title`, `info`) values ('$title', '$info')";
	if (mysql_query($query))
	{
		$query = "select `id` from `song` where `title` = '$title' and `info` = '$info'";
		$result = mysql_fetch_array(mysql_query($query));
		echo $result[0];
	}
	else echo "FAIL";
}
else if ($action == "delete")
{
	$query = "select `id` from `songLink` where `song` = '$id'";
	$sql = mysql_query($query);
	while ($result = mysql_fetch_array($sql)) repertoire_delfile($result[0]) || die("NODEL");
	$query = "delete from `song` where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else echo "FAIL";
}
else if ($action == "update")
{
	$query = "update `song` set `title` = '$title', `info` = '$info' where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else echo "FAIL";
}
else if ($action == "current")
{
	$query = "update `song` set `current` = '$current' where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else echo "FAIL";
}
else echo "FAIL";
?>