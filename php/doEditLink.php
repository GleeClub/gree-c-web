<?php
require_once('functions.php');

$id = $_POST['id'];
$action = $_POST['action'];
$name = $_POST['name'];
$type = $_POST['type'];
$target = $_POST['target'];
$song = $_POST['song'];
if (! $USER || ! hasPermission("edit-repertoire")) err("UNAUTHORIZED");
if ($action == "new")
{
	echo "OK " . query("insert into `songLink` (`type`, `name`, `target`, `song`) values (?, '', '', ?)", [$type, $song], QID);
}
else if ($action == "upload")
{
	$file = $_FILES['file'];
	if ($file['error'] > 0) err($file['error']);
	$name = $file['name'];
	if ($name == '' || preg_match('/[^a-zA-Z0-9_., -]/', $name) || preg_match('/^\./', $name)) err("The file being uploaded has an invalid name.  Try using an alphanumeric name.");
	if (! move_uploaded_file($file['tmp_name'], $docroot_external . $musicdir . '/' . $name)) err("The server failed to upload the file", "move_uploaded_file failed");
	query("update `songLink` set `target` = ? where `id` = ?", [$name, $id]);
	echo "OK $musicdir/$name";
}
else if ($action == "rmfile")
{
	repertoire_delfile($id);
	echo "OK";
}
else if ($action == "delete")
{
	repertoire_delfile($id); // Remove associated file
	query("delete from `songLink` where `id` = ?", [$id]);
	echo "OK";
}
else if ($action == "update")
{
	$result = query("select `songLink`.`type`, `mediaType`.`storage` from `songLink`, `mediaType` where `songLink`.`id` = ? and `mediaType`.`typeId` = `songLink`.`type`", [$id], QONE);
	if (! $result) err("Song link does not exist");
	$type = $result["type"];
	$storage = $result["storage"];
	if ($type == 'video') { if (! preg_match('/^[A-Za-z0-9_-]{11}$/', $target)) err("Invalid YouTube ID provided"); }
	else if ($storage == 'remote')
	{
		if (! preg_match('/^http:\/\//', $target)) $target = 'http://$target';
	}
	if ($storage == "remote") query("update `songLink` set `name` = ?, `target` = ? where `id` = ?", [$name, $target, $id]);
	else query("update `songLink` set `name` = ? where `id` = ?", [$name, $id]);
	echo "OK";
}
else err("The requested action is invalid", "Invalid action selected in doEditLink");
?>
