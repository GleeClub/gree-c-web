<?php
require_once("general/vars.php");

require_once('general/utility.php');
require_once('general/attendance.php');
require_once('general/carpools.php');
require_once('general/events.php');

if ($CHOIR) require_once('choir/' . $CHOIR . '/base.php');
?>
