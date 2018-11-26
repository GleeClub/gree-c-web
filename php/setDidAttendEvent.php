<?php
require_once('./functions.php');

if (! isset($_POST['eventNo'])) err("Missing event ID");
$eventNo = $_POST["eventNo"];
$memberID = $_POST["email"];
$didAttend = $_POST["didAttend"];

//make a new attends relationship, if it doesn't already exist
if (query("select `didAttend` from `attends` where `memberID` = ? and `eventNo` = ?", [$memberID, $eventNo], QCOUNT) == 0)
	query("insert into `attends` (`memberID`, `shouldAttend`, `didAttend`, `eventNo`, `minutesLate`, `confirmed`) values (?, ?, ?, ?, ?, ?)", [$memberID, 0, $didAttend, $eventNo, 0, 1]);
//otherwise, update the existing relationship
else query("update `attends` set `confirmed` = ?, `didAttend` = ? where `memberID` = ? and `eventNo` = ?'", [1, $didAttend, $memberID, $eventNo]);

//get the user's first and last name
$member = query("select * from `member` where `email` = ?", [$memberID], QONE);
if (! $member) err("No such member");
$firstName = $member["firstName"];
$lastName = $member["lastName"];

//get the updated attendance info and th recalculated grade
echo getEventAttendanceRows($eventNo);
?>
