<?php
require_once('functions.php');
$id = $_POST['id'];
$newname = $_POST['newname'];
$private = $_POST['private'];
$public = $_POST['public'];
if (! $USER || ! hasPermission("edit-minutes")) die("UNAUTHORIZED");
if (! $CHOIR) die("NO_CHOIR");

if ($id == '') $id = query("insert into `minutes` (`choir`, `date`, `name`, `private`, `public`)  values (?, curdate(), ?, ?, ?)", [$CHOIR, $newname, $private, $public], QID); // New record
else if ($newname == ".DELETE") query("delete from `minutes` where `id` = ?", [$id]);
else query("update `minutes` set `name` = ?, `private` = ?, `public` = ? where `id` = ?", [$newname, $private, $public, $id]); // Edit existing record
echo "OK\n$id";
?>
