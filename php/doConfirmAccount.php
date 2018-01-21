<?php
require_once('functions.php');
if (! $CHOIR) die("No choir selected");

$res = mysql_query("select * from `activeSemester` where `member` = '$USER' and `semester` = '$SEMESTER' and `choir` = '$CHOIR'");
if (mysql_num_rows($res) != 0) die("Already confirmed");
$loc = mysql_real_escape_string($_POST['location']);
$reg = mysql_real_escape_string($_POST['registration']);
$sect = mysql_real_escape_string($_POST['section']);
if ($reg != "class" && $reg != "club") die("Invalid registration \"$reg\"");
if (! mysql_query("insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`, `section`) values ('$USER', '$SEMESTER', '$CHOIR', '$reg', '$sect')")) die("Error confirming member: " . mysql_error());
if (! mysql_query("update `member` set `location` = '$loc' where `email` = '$USER'")) die("Error setting location: " . mysql_error());
if (! mysql_query("insert ignore into `attends` (`memberID`, `eventNo`) select '$USER', `eventNo` from `event` where `semester` = '$SEMESTER' and `choir` = '$CHOIR' and (`type` != 'sectional' or `section` = $sect or `section` = 0)")) die("Error setting attendance relationships: " . mysql_error());
echo "OK";
?>
