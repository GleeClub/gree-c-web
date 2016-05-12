<?php
require_once('./functions.php');
$eventNo = mysql_real_escape_string($_POST['eventNo']);

if (! attendancePermission($USER, $eventNo)) die("Access denied");
if (! isset($eventNo)) die("Missing event number");

$sql = "select `name`, `section` from `event` where `eventNo` = '$eventNo'";
$event = mysql_fetch_array(mysql_query($sql));
$name = $event['name'];

$html ="<div class='pull-right'><button class='btn' onclick='excuseall($eventNo)'>Excuse Unconfirmed</button></div>
<p style='text-align: center; font-weight: bold;'>$name Attendance</p> 
<p id='attendanceList'><table id='$eventNo"."_table'>" . getEventAttendanceRows($eventNo) . "</table></p>";

echo $html;
?>
