<?php
require_once('functions.php');
require_once('events.php');
if (! isset($_POST['eventNo'])) err("Missing event number");
$eventNo = $_POST['eventNo'];
doRemoveEvent($eventNo)
?>
