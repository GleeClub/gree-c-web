<?php
require_once('functions.php');
mysql_set_charset("utf8");
$id = mysql_real_escape_string($_POST['id']);
$action = mysql_real_escape_string($_POST['action']);
$title = mysql_real_escape_string($_POST['name']);
$info = mysql_real_escape_string($_POST['desc']);
$note = mysql_real_escape_string($_POST['note']);
$current = mysql_real_escape_string($_POST['current']);
if (! $CHOIR) die("Choir is not set");
if (! $USER || ! isOfficer($USER)) die("UNAUTHORIZED");
if ($action == "add")
{
	$query = "insert into `song` (`choir`, `title`, `info`) values ('$CHOIR', '$title', '$info')";
	if (mysql_query($query)) echo mysql_insert_id();
	else echo mysql_error();
}
else if ($action == "delete")
{
	$query = "select `id` from `songLink` where `song` = '$id'";
	$sql = mysql_query($query);
	while ($result = mysql_fetch_array($sql)) repertoire_delfile($result[0]) || die("NODEL");
	$query = "delete from `song` where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else echo mysql_error();
}
else if ($action == "update")
{
	$query = "update `song` set `title` = '$title', `info` = '$info' where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else echo mysql_error();
}
else if ($action == "current")
{
	$query = "update `song` set `current` = '$current' where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else echo mysql_error();
}
else if ($action == "key")
{
	$query = "update `song` set `key` = '$note' where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else echo mysql_error();
}
else if ($action == "pitch")
{
	$query = "update `song` set `pitch` = '$note' where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else echo mysql_error();
}
else echo "BAD_ACTION";
?>
