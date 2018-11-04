<?php
require_once('functions.php');
$id = $_POST['id'];
$type = $_POST['type'];
//if (! $USER) die("You must be logged in to view minutes.");

if (isset($type))
{
	if ($type == "name")
	{
		$res = query("select `name` from `minutes` where `id` = ?", [$id], QONE);
		if (! $res) die("Minutes not found");
		echo $res["name"];
	}
	else die("Unknown type");
	exit(0);
}
$result = query("select count(`public`) as `n` from `minutes` where `id` = ?", [$id], QONE);
if ($result['n'] == 0) die("The minutes you requested do not exist.");
if (hasPermission("view-complete-minutes") && ! isset($_POST['public'])) echo query("select `private` from `minutes` where `id` = ?", [$id], QONE)["private"];
else echo query("select `public` from `minutes` where `id` = ?", [$id], QONE)["public"];
?>
