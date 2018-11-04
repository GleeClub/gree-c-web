<?php
require_once('./functions.php');
$eventNo = $_POST['eventNo'];

if (! hasEventPermission("view-attendance", $eventNo)) die("Access denied");
if (! isset($eventNo)) die("Missing event number");
$res = query("select `name` from `event` where `eventNo` = ?", [$eventNo], QONE);
if (! $res) die("That event does not exist");
$name = $res["name"];

$html ="<div class='pull-right'><button class='btn' onclick='excuseall($eventNo)'>Excuse Unconfirmed</button></div>
<p style='text-align: center; font-weight: bold;'>$name Attendance</p>
<p id='attendanceList'><table id='$eventNo"."_table'>" . getEventAttendanceRows($eventNo) . "</table></p>";

echo $html;
?>
