<?php
require_once('functions.php');
$request = $_POST['request'];
if ($request != "typeid" && $request != "name" && $request != "storage") die("Invalid request type \"$request\"");
$i = 0;
foreach (query("select `$request` from `mediaType` order by `order` asc", [], QALL) as $result)
{
	if ($i++ != 0) echo "\n";
	echo $result[$request];
}
?>
