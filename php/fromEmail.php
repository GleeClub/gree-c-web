<?php
require_once('functions.php');

if (! isset($USER)) die("<html><head><title>Log In</title></head><body>You must be logged in to respond via this link.  <a href=\"$BASEURL\">Log in</a> and then try again.</body></html>");

$event = $_GET['id'];
if ($_GET['attend'] == 'true') $attend = true;
else if ($_GET['attend'] == 'false') $attend = false;
else die("BAD_REQUEST");

$result = query("select * from `event` where `eventNo` = ?", [$event], QONE);
if (! $result) die("The event does not appear to exist.");
if(! ($result['type'] == 'volunteer' || $result['type'] == 'tutti')) die("The event is not a gig.");
if((strtotime($result["callTime"]) - time()) < 86400) die("You cannot respond within 24 hours of an event!");
if($result['type'] == 'tutti' && ! $attend) die("Nice try.  Try submitting an absence request.");

query("update `attends` set `shouldAttend` = ?, `confirmed` ='1' where `memberID` = ? and `eventNo` = ?", [$attend, $USER, $event]);
header("Location: $BASEURL/#event:$event");
?>
