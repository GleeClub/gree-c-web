<?php

require_once('./functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

$userEmail = $_COOKIE['email'];
if (! isset($_POST['eventNo'])) die("Missing event number");

function ensure_attends($memberID, $eventNo)
{
	$attendses = mysql_query("select * from attends where memberID='$memberID' and eventNo='$eventNo'");
	if(mysql_num_rows($attendses)==0) mysql_query("INSERT INTO attends (memberID, shouldAttend, didAttend, eventNo, minutesLate, confirmed) VALUES ('$memberID', '0', '0', '$eventNo', '0', '1')");
}

$eventNo = $_POST['eventNo'];
$memberID = $_POST['email'];
$action = $_POST['action'];
$value = mysql_real_escape_string($_POST['value']);
$position = positionFromEmail($userEmail);
if ($position != "President" && $position != "VP")
{
	if ($userEmail != $memberID) die("Access denied");
	$event = mysql_fetch_array(mysql_query("select * from `event` where `eventNo` = '$eventNo'"));
	if ($action == "did") die();
	if ($action == "should" && strtotime($event['callTime'] < strtotime('+1 day')) && $value != 1) die();
	if ($action == "should" && $event['type'] != 3 && $value != 1) die();
	if ($action == "late") die();
	if ($action == "confirmed" && $value == "0") die();
	if ($action == "excuse_all") die();
}
if ($action == "should" || $action == "did")
{
	$field = '';
	if ($action == "should") $field = "shouldAttend";
	else if ($action == "did") $field = "didAttend";

	ensure_attends($memberID, $eventNo);
	mysql_query("update `attends` set `confirmed` = '1', `$field` = '$value' where `memberID` = '$memberID' and `eventNo` = '$eventNo'");
}
else if ($action == "late")
{
	ensure_attends($memberID, $eventNo);
	mysql_query("update `attends` set `minutesLate` = '$value' where `memberID` = '$memberID' and `eventNo` = '$eventNo'");
}
else if ($action == "confirmed")
{
	ensure_attends($memberID, $eventNo);
	mysql_query("update `attends` set `confirmed` = '$value' where `memberID` = '$memberID' and `eventNo` = '$eventNo'");
}
else if ($action == "excuse_all")
{
	mysql_query("update `attends` set `shouldAttend` = '0' where `eventNo` = '$eventNo' and `confirmed` = '0'");
}
else die("Unknown action");

//get the updated attendance info for this one attends relationship
echo getSingleEventAttendanceRow($eventNo,$memberID);
?>

