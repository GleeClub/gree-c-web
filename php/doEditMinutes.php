<?php
require_once('functions.php');
$id = mysql_real_escape_string($_POST['id']);
$newname = mysql_real_escape_string($_POST['newname']);
$private = mysql_real_escape_string($_POST['private']);
$public = mysql_real_escape_string($_POST['public']);
if (! $USER || ! hasPermission("edit-minutes")) die("UNAUTHORIZED");
if (! $CHOIR) die("NO_CHOIR");

if ($id == '') $query = "insert into `minutes` (`choir`, `date`, `name`, `private`, `public`)  values ('$CHOIR', curdate(), '$newname', '$private', '$public')"; // New record
else if ($newname == ".DELETE") $query = "delete from `minutes` where `id` = '$id'";
else $query = "update `minutes` set `name` = '$newname', `private` = '$private', `public` = '$public' where `id` = '$id'"; // Edit existing record
if (mysql_query($query))
{
	if ($id == '') $id = mysql_insert_id();
	echo "OK\n$id";
}
else echo "FAIL";
?>
