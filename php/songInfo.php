<?php
require_once('functions.php');
$results = query("select `title`, `info` from `song` where `id` = ?", [$songid], QONE);
if (! $results) die("Song not found");
if ($request == "name") echo $results["title"];
else if ($request == "desc") echo $results["info"];
?>
