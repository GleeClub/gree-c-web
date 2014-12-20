<?php
require_once('functions.php');
$songid = mysql_real_escape_string($_POST['id']);
$request = mysql_real_escape_string($_POST['item']);
$query = "select `title`, `info` from `song` where `id` = '$songid'";
$results = mysql_fetch_array(mysql_query($query));
if ($request == "name") echo $results[0];
else if ($request == "desc") echo $results[1];
?>