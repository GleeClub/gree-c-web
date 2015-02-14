<?php
require_once('functions.php');
$userEmail = mysql_real_escape_string($_COOKIE['email']);
global $CUR_SEM;

$loc = mysql_real_escape_string($_POST['location']);
$reg = mysql_real_escape_string($_POST['registration']);
if (! mysql_query("insert into `activeSemester` (`member`, `semester`) values ('$userEmail', '$CUR_SEM')")) die("Error confirming member");
if (! mysql_query("update `member` set `registration` = '$reg', `location` = '$loc' where `email` = '$userEmail'")) die("Error confirming member");
if (! mysql_query("insert ignore into `attends` (`memberID`, `eventNo`) select '$userEmail', `eventNo` from `event` where `semester` = '$CUR_SEM' and not(`type` = 2)")) die("Error setting attendance relationships");
echo "OK";
?>
