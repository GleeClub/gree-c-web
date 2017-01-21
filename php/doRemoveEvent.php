<?php
require_once('functions.php');

if (! $USER) die("Not logged in");
if (! isset($_POST['eventNo'])) die("Missing event number");
$eventNo = mysql_real_escape_string($_POST['eventNo']);
$query = mysql_query("select `type` from `event` where `eventNo` = $eventNo");
if (! $query) die(mysql_error());
$row = mysql_fetch_array($query);
if (! canEditEvents($USER, $row["type"])) die("Permission denied");
mysql_query("DELETE FROM `event` WHERE `eventNo` = $eventNo LIMIT 1");
?>

