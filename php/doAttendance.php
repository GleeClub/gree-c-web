<?php
require_once('./functions.php');

if (! isset($_POST['eventNo'])) err("Missing event number");

function ensure_attends($memberID, $eventNo)
{
	$attendses = query("select * from `attends` where `memberID` = ? and `eventNo` = ?", [$memberID, $eventNo], QCOUNT);
	if ($attendses == 0) query("insert into `attends` (`memberID`, `shouldAttend`, `didAttend`, `eventNo`, `minutesLate`, `confirmed`) values (?, ?, ?, ?, ?, ?)", [$memberID, 0, 0, $eventNo, 0, 1]);
}

$eventNo = $_POST['eventNo'];
$memberID = $_POST['email'];
$action = $_POST['action'];
$value = $_POST['value'];
if (! hasPermission("edit-attendance"))
{
	if ($USER != $memberID) err("Access denied");
	$event = query("select * from `event` where `eventNo` = ?", [$eventNo], QONE);
	if (! $event) err("Event not found");
	if ($action == "did") err();
	if ($action == "should" && strtotime($event["callTime"] < strtotime("+1 day")) && $value != 1) err();
	if ($action == "should" && $event["type"] != "volunteer" && $value != 1) err();
	if ($action == "late") err();
	if ($action == "confirmed" && $value == "0") err();
	if ($action == "excuse_all") err();
}
if ($action == "should" || $action == "did")
{
	if ($action == "should") $field = "shouldAttend";
	else if ($action == "did") $field = "didAttend";
	else err("Invalid action \"$action\"");

	ensure_attends($memberID, $eventNo);
	query("update `attends` set `$field` = ? where `memberID` = ? and `eventNo` = ?", [$value, $memberID, $eventNo]);
}
else if ($action == "late")
{
	ensure_attends($memberID, $eventNo);
	query("update `attends` set `minutesLate` = ? where `memberID` = ? and `eventNo` = ?", [$value, $memberID, $eventNo]);
}
else if ($action == "confirmed")
{
	ensure_attends($memberID, $eventNo);
	query("update `attends` set `confirmed` = ? where `memberID` = ? and `eventNo` = ?", [$value, $memberID, $eventNo]);
}
else if ($action == "excuse_all")
{
	query("update `attends` set `shouldAttend` = '0' where `eventNo` = ? and `confirmed` = '0'", [$eventNo]);
	exit(0);
}
else err("Unknown action");

//get the updated attendance info for this one attends relationship
echo getSingleEventAttendanceRow($eventNo,$memberID);
?>

