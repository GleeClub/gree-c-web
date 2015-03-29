<?php
require_once('functions.php');

$user = getuser();
if (! isset($user))
{
	header("Location: /buzz/index.php");
	exit(1);
}

$event = mysql_real_escape_string($_GET['id']);
if ($_GET['attend'] == 'true') $attend = true;
else if ($_GET['attend'] == 'false') $attend = false;
else die("BAD_REQUEST");

$query = mysql_query("select * from `event` where `eventNo` = '$event'");
if (mysql_num_rows($query) != 1) die("The event does not appear to exist.");
$result = mysql_fetch_array($query);
if(! ($result['type'] == 3 || $result['type'] == 4)) die("The event is not a gig.");
if((strtotime($result["callTime"]) - time()) < 86400) die("You cannot respond within 24 hours of an event!");
if($result['type'] == 4 && ! $attend) die("Nice try.  Try submitting an absence request.");

mysql_query("update `attends` set `shouldAttend` = '$attend', `confirmed` ='1' where `memberID` = '$user' AND `eventNo` = '$event'");
header("Location: /buzz/index.php#event:$event");
?>
