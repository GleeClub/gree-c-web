<?php
require_once('functions.php');
$userEmail = mysql_real_escape_string(getuser());

$res = mysql_query("select * from `activeSemester` where `member` = '$userEmail' and `semester` = '$CUR_SEM'");
if (mysql_num_rows($res) != 0) die("Already confirmed");
$loc = mysql_real_escape_string($_POST['location']);
$reg = mysql_real_escape_string($_POST['registration']);
if ($reg != "class" && $reg != "club") die("Invalid registration");
if (! mysql_query("insert into `activeSemester` (`member`, `semester`, `enrollment`) values ('$userEmail', '$CUR_SEM', '$reg')")) die("Error confirming member");
if (! mysql_query("update `member` set `location` = '$loc' where `email` = '$userEmail'")) die("Error setting location");
if (! mysql_query("insert ignore into `attends` (`memberID`, `eventNo`) select '$userEmail', `eventNo` from `event` where `semester` = '$CUR_SEM' and not(`type` = 2)")) die("Error setting attendance relationships");
echo "OK";
?>
