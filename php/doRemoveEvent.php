<?php
require_once('functions.php');

if (! getuser()) die("Not logged in");
if (! isOfficer(getuser())) die("Not an officer");
if (! isset($_POST['eventNo'])) die("Missing event number");
$eventNo = mysql_real_escape_string($_POST['eventNo']);
$sql = "DELETE FROM `event` WHERE `eventNo` = $eventNo LIMIT 1";
mysql_query($sql);

?>
