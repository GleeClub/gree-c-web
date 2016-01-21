<?php
require_once('./functions.php');
$userEmail = getuser();

if(! isset($_POST['eventNo'])) die("No event number provided");
$eventNo = mysql_real_escape_string($_POST['eventNo']);
$replacement = mysql_real_escape_string($_POST['replacement']);
$reason = mysql_real_escape_string($_POST['reason']);

//if they didn't specify a reason, don't let them off the hook
if ($reason == "") die("You need a reason.  Try again.<br><div class='btn' id='retryAbsenceButton' value='$eventNo'>try again</div>");
$attendanceOfficers = implode(", ", getPosition("Vice President")) . ", " . implode(", ", getPosition("President"));
if (! mysql_query("insert into `absencerequest` (reason,memberID,eventNo) values ('$reason','$userEmail','$eventNo')")) die("Query failed: " . mysql_error());
$mail = sendMessageEmail($attendanceOfficers, $userEmail, 'Name:  ' . fullNameFromEmail($userEmail) . '<br>Event:  ' . getEventName($_POST['eventNo']) . '<br>Reason:  ' . $reason, 'Absence Request on Gree-C-Web');
echo "<p>Your request has been submitted.  You lazy bum!</p>";
?>
