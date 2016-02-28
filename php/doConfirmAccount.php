<?php
require_once('functions.php');
$userEmail = mysql_real_escape_string(getuser());
$choir = getchoir();
if (! $choir) die("No choir selected");

$res = mysql_query("select * from `activeSemester` where `member` = '$userEmail' and `semester` = '$CUR_SEM' and `choir` = '$choir'");
if (mysql_num_rows($res) != 0) die("Already confirmed");
$loc = mysql_real_escape_string($_POST['location']);
$reg = mysql_real_escape_string($_POST['registration']);
if ($reg != "class" && $reg != "club") die("Invalid registration");
if (! mysql_query("insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`) values ('$userEmail', '$CUR_SEM', '$choir', '$reg')")) die("Error confirming member: " . mysql_error());
if (! mysql_query("update `member` set `location` = '$loc' where `email` = '$userEmail'")) die("Error setting location: " . mysql_error());
if (! mysql_query("insert ignore into `attends` (`memberID`, `eventNo`) select '$userEmail', `eventNo` from `event` where `semester` = '$CUR_SEM' and `choir` = '$choir' and not(`type` = 'sectional')")) die("Error setting attendance relationships: " . mysql_error());
echo "OK";
?>
