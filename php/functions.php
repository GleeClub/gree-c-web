<?php
require_once('general/variables.php'); // THIS IS IMPORTANT because every PHP script uses functions.php to indirectly include variables.php!

require_once('general/utility.php');
require_once('general/attendance.php');
require_once('general/carpools.php');
require_once('general/events.php');
require_once('general/messaging.php');

if (getchoir()) require_once('choir/' . getchoir() . '/base.php');
?>
