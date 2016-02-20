<?php
require_once('functions.php');

if (! getuser()) die("Not logged in");
if (! canEditEvents(getuser())) die("Permission denied");
if (! isset($_POST['eventNo'])) die("Missing event number");
$eventNo = mysql_real_escape_string($_POST['eventNo']);
$sql = "DELETE FROM `event` WHERE `eventNo` = $eventNo LIMIT 1";
mysql_query($sql);

?>
