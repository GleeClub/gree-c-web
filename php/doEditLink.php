<?php
require_once('functions.php');

$id = $_POST['id'];
$action = $_POST['action'];
$name = $_POST['name'];
$type = $_POST['type'];
$target = $_POST['target'];
$song = $_POST['song'];
if (! $USER || ! hasPermission("edit-repertoire")) die("UNAUTHORIZED");
if ($action == "new")
{
	echo query("insert into `songLink` (`type`, `name`, `target`, `song`) values (?, '', '', ?)", [$type, $song], QID);
}
else if ($action == "upload")
{
	$file = $_FILES['file'];
	if ($file['error'] > 0) die($file['error']);
	$name = $file['name'];
	if ($name == '' || preg_match('/[^a-zA-Z0-9_., -]/', $name) || preg_match('/^\./', $name)) die("BAD_FNAME");
	if (! move_uploaded_file($file['tmp_name'], $docroot_external . $musicdir . '/' . $name)) die("BAD_UPLOAD");
	query("update `songLink` set `target` = ? where `id` = ?", [$name, $id]);
	echo "OK $musicdir/$name";
}
else if ($action == "rmfile")
{
	if (! repertoire_delfile($id)) die("NODEL");
	echo "OK";
}
else if ($action == "delete")
{
	if (! repertoire_delfile($id)) die("NODEL"); // Remove associated file
	query("delete from `songLink` where `id` = ?", [$id]);
	echo "OK";
}
else if ($action == "update")
{
	$result = query("select `songLink`.`type`, `mediaType`.`storage` from `songLink`, `mediaType` where `songLink`.`id` = ? and `mediaType`.`typeId` = `songLink`.`type`", [$id], QONE);
	if (! $result) die("Song link does not exist");
	$type = $result["type"];
	$storage = $result["storage"];
	if ($type == 'video') { if(! preg_match('/^[A-Za-z0-9_-]{11}$/', $target)) die("BAD_YOUTUBE"); }
	else if ($storage == 'remote')
	{
		if (! preg_match('/^http:\/\//', $target)) $target = 'http://$target';
	}
	if ($storage == "remote") query("update `songLink` set `name` = ?, `target` = ? where `id` = ?", [$name, $target, $id]);
	else query("update `songLink` set `name` = ? where `id` = ?", [$name, $id]);
	echo "OK";
}
else die("FAIL");
?>
