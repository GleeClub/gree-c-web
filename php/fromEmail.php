<?php
require_once('functions.php');

if (! isset($USER) || $USER === null) err("<html><head><title>Log In</title></head><body>You must be logged in to respond via this link.  <a href=\"$BASEURL\">Log in</a> and then try again.</body></html>");

$event = $_GET["id"];
if ($_GET["attend"] == "true") $attend = 1;
else if ($_GET["attend"] == "false") $attend = 0;
else err("Invalid value for property \"attend\"");

$result = query("select * from `event` where `eventNo` = ?", [$event], QONE);
if (! $result) err("This event does not appear to exist.");
if (! ($result["type"] == "volunteer" || $result["type"] == "tutti")) err("This event is not a gig.");
if (! $attend && ($result["type"] == "tutti" || (strtotime($result["callTime"]) - time()) < 86400))
{ # We only care about restricting responses when a) the response is "won't attend" AND b) the event is either tutti OR within 24 hours
	$existing = query("select `shouldAttend` from `attends` where `memberID` = ? and `eventNo` = ?", [$USER, $event], QONE);
	if ($existing && $existing["shouldAttend"] == 1) err("Responses for this event are closed.  Try submitting an absence request.");
}

query("update `attends` set `shouldAttend` = ?, `confirmed` = '1' where `memberID` = ? and `eventNo` = ?", [$attend, $USER, $event]);
header("Location: $BASEURL/#event:$event");
?>
