<?php
require_once('functions.php');
$id = $_POST['id'];
$action = $_POST['action'];
$title = $_POST['name'];
$info = $_POST['desc'];
$note = $_POST['note'];
$current = $_POST['current'];
if (! $CHOIR) die("Choir is not set");
if (! $USER || ! hasPermission("edit-repertoire")) die("UNAUTHORIZED");
if ($action == "add") echo query("insert into `song` (`choir`, `title`, `info`) values (?, ?, ?)", [$CHOIR, $title, $info], QID);
else if ($action == "delete")
{
	foreach(query("select `id` from `songLink` where `song` = ?", [$id], QALL) as $result) repertoire_delfile($result["id"]);
	query("delete from `song` where `id` = ?", [$id]);
}
else if ($action == "update")
	query("update `song` set `title` = ?, `info` = ? where `id` = ?", [$title, $info, $id]);
else if ($action == "current")
	query("update `song` set `current` = ? where `id` = ?", [$current, $id]);
else if ($action == "key")
	query("update `song` set `key` = ? where `id` = ?", [$note, $id]);
else if ($action == "pitch")
	query("update `song` set `pitch` = ? where `id` = ?", [$note, $id]);
else echo "Unknown action \"$action\"";
?>
