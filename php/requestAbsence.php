<?php
require_once('./functions.php');

if(! isset($_POST['eventNo'])) die("No event number provided");
$eventNo = mysql_real_escape_string($_POST['eventNo']);
$replacement = mysql_real_escape_string($_POST['replacement']);
$reason = mysql_real_escape_string($_POST['reason']);

//if they didn't specify a reason, don't let them off the hook
if ($reason == "") die("You need a reason.  Try again.<br><div class='btn' id='retryAbsenceButton' value='$eventNo'>try again</div>");
$attendanceOfficers = implode(", ", getPosition("Vice President")) . ", " . implode(", ", getPosition("President"));
# TODO Check for duplicate queries and display an error message if a request has already been submitted for this event
if (! mysql_query("insert into `absencerequest` (reason,memberID,eventNo) values ('$reason','$USER','$eventNo')")) die("Query failed: " . mysql_error());
$mail = sendMessageEmail($attendanceOfficers, $USER, 'Name:  ' . fullNameFromEmail($USER) . '<br>Event:  ' . getEventName($eventNo) . '<br>Reason:  ' . $reason, 'Absence Request on Gree-C-Web');
echo "<p>Your request has been submitted.  You lazy bum!</p>";
?>
