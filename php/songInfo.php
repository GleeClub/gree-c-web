<?php
require_once('functions.php');
$results = query("select `title`, `info` from `song` where `id` = ?", [$_POST["id"]], QONE);
if (! $results) die("Song not found");
$request = $_POST["item"];
if ($request == "name") echo $results["title"];
else if ($request == "desc") echo $results["info"];
?>
