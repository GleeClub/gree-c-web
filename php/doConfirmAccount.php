<?php
require_once('functions.php');
if (! $CHOIR) die("No choir selected");

if (query("select * from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$USER, $SEMESTER, $CHOIR], QCOUNT) != 0) die("Already confirmed");
$loc = $_POST['location'];
$reg = $_POST['registration'];
$sect = $_POST['section'];
if ($reg != "class" && $reg != "club") die("Invalid registration \"$reg\"");
query("insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`, `section`) values (?, ?, ?, ?, ?)", [$USER, $SEMESTER, $CHOIR, $reg, $sect]);
query("update `member` set `location` = ? where `email` = ?", [$loc, $USER]);
query("insert ignore into `attends` (`memberID`, `eventNo`, `shouldAttend`) select ?, `eventNo`, `defaultAttend` from `event` where `semester` = ? and `choir` = ? and (`type` != 'sectional' or `section` = ? or `section` = 0)", [$USER, $SEMESTER, $CHOIR, $sect]);
echo "OK";
?>
