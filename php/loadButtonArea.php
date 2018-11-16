<?php
require_once('functions.php');

if (! isset($_POST['eventNo'])) die("Missing event ID");
$eventNo = $_POST['eventNo'];

//get the event type
$event = query("select *, unix_timestamp(`callTime`) as `call` from `event` where `eventNo` = ?", [$eventNo], QONE);
if (! $event) die("That event does not exist");
$type = $event["type"];

//determine whether the user said they were attending or not attending
$attending = $_POST['attending'];

if ($type != "volunteer" && $attending != 1) die("You can only confirm not attending for volunteer events.");

if ($event["call"] < time() + 86400 && $attending != 1)
{
	// Prevent changing to not attending less than 24 hours before call
	echo '<span class="label label-important">Deadline is past</span>' . buttonArea($eventNo, $type);
	return;
}

//update the attends relationship
query("update `attends` set `shouldAttend` = ?, `confirmed` = ? where `memberID` = ? and `eventNo` = ?", [$attending, 1, $USER, $eventNo]);

//then echo the new buttons based on the new attends relationship
echo buttonArea($eventNo, $type);
?>
