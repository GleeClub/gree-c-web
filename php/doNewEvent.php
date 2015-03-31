<?php
require_once('functions.php');
$userEmail = getuser();

function eventEmail($eventNo,$type)
{
	GLOBAL $BASEURL;
	$sql = "select * from eventType where typeNo='$type'";
	$eventType  =  mysql_fetch_array(mysql_query($sql));
	$typeName = $eventType['typeName'];
	
	$eventResults = mysql_fetch_array(mysql_query("SELECT * from `event` where `eventNo` = '$eventNo'"));
	$eventName = $eventResults['name'];
	$eventTypeNo = $eventResults['type'];
	$eventTime = $eventResults['callTime'];
	$eventReleaseTime = $eventResults['releaseTime'];
	$eventComments = $eventResults['comments'];
	$eventLocation = $eventResults['location'];
	$gigResults = mysql_fetch_array(mysql_query("select * from `gig` where `eventNo` = '$eventNo'"));
	$uniformCode = $gigResults['uniform'];
	$uniformResults = mysql_fetch_array(mysql_query("select `name` from `uniform` where `id` = '$uniformCode'"));
	$eventUniform = $uniformResults['name'];

	$eventTime = strtotime($eventTime);
	$eventTimeDisplay = date("D, M d g:i a", $eventTime);

	$eventReleaseTime = strtotime($eventReleaseTime);
	$eventReleaseTimeDisplay = date("D, M d g:i a", $eventReleaseTime);
	
	$typeResults = mysql_fetch_array(mysql_query("SELECT * from `eventType` where typeNo=$eventTypeNo"));
	$eventType = $typeResults['typeName'];

	$redirectURL = "$BASEURL/php/fromEmail.php";
	$eventUrl = "$BASEURL/#event:$eventNo";

	//$recipient = "Matthew Schauer <awesome@gatech.edu>";
	$recipient = "Glee Club <gleeclub@lists.gatech.edu>";
	$subject = "New Glee Club Event";
	$headers = 'Content-type:text/html;' . "\n" .
		'Reply-To: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\n" .
		'From: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\n" .
		'X-Mailer: PHP/' . phpversion();
	$message = "<html><head></head><body>
		<h2><a href='$eventUrl'>$eventName</a></h2>
		<p><b>$typeName</b> from <b>$eventTimeDisplay</b> to $eventReleaseTimeDisplay at <b>$eventLocation</b></p>
		<p>Uniform:  $eventUniform</p>
		<p>$eventComments</p>";

	$message .= "<form action='$redirectURL' method='get'><input type='hidden' value='$eventNo' name='id' />";
	if ($typeName == "Volunteer Gig") $confirm = "I will attend";
	else if ($typeName == "Tutti Gig") $confirm = "Confirm I will attend";
	$yesform = "<button type='submit' value='true' name='attend'>$confirm</button>";
	$noform = "<button type='submit' value='false' name='attend'>I will not attend</button>";
	if ($typeName == "Volunteer Gig") $message .= $yesform . $noform;
	else if ($typeName == "Tutti Gig") $message .= $yesform;
	$message .= '</form></body></html>';
	if (! mail($recipient, $subject, $message, $headers)) die("Failed to send event email");
}

// Add to event, and everyone's attending
function createEvent($name, $type, $call, $done, $location, $points, $sem, $comments, $gigcount, $section)
{
	global $CUR_SEM;
	if (! mysql_query("insert into event (name, callTime, releaseTime, points, comments, type, location, semester, gigcount) values ('$name', '$call', '$done', '$points', '$comments', '$type', '$location', '$sem', '$gigcount')")) die("Failed to create event");
	$eventNo = mysql_insert_id();

	if ($section >= 0 && strtotime($call) > strtotime('+48 hours')) // -1 for nobody to attend, 0 for everyone to attend
	{
		if ($section == 0) { if (! mysql_query("insert into `attends` (`memberID`, `eventNo`) select `member`, '$eventNo' from `activeSemester` where `semester` = '$CUR_SEM'")) die("Failed to insert attends relations for event"); }
		else
		{
			if (! mysql_query("update `event` set `section` = '$section' where `eventNo` = '$eventNo'")) die("Failed to set section");
			if (! mysql_query("insert into `attends` (`memberID`, `eventNo`) select `email`, '$eventNo' from `member` where `section` = '2' and `email` in (select `member` from `activeSemester` where `semester` = '$CUR_SEM')")) die("Failed to create attends relation for sectional");
		}
	}
	return $eventNo;
}

// Add to event and gig, and everyone's attending
function createGig($name, $tutti, $call, $perform, $done, $location, $points, $sem, $comments, $uniform, $cname, $cemail, $cphone, $price, $gigcount, $public, $summary, $description)
{
	$eventNo = createEvent($name, ($tutti ? 4 : 3), $call, $done, $location, $points, $sem, $comments, $gigcount, 0);

	$publicval = $public ? 1 : 0;
	if (! mysql_query("insert into `gig` (eventNo, performanceTime, uniform, cname, cemail, cphone, price, public, summary, description) values ('$eventNo', '$perform', '$uniform', '$cname', '$cemail', '$cphone', '$price', '$publicval', '$summary', '$description')")) die("Failed to create gig");
	return $eventNo;
}

// Add to event, and only one section is attending if defined by $section
function createRehearsal($name, $type, $call, $done, $location, $points, $sem, $comments, $section)
{
	if ($type != 1 && $type != 2) die("Internal error 1 in createRehearsal, type is $type");
	if ($type == 1 && $section != 0) die("Internal error 2 in createRehearsal, type is $type");

	$attend = 0;
	if ($type == 2) $attend = -1;
	if ($section) $attend = $section;
	return createEvent($name, $type, $call, $done, $location, $points, $sem, $comments, 0, $attend);
}

foreach ($_POST as &$value) $value = mysql_real_escape_string($value);
$eventNo = -1;
$type = $_POST['type'];
$repeat = $_POST['repeat'];

if (! valid_date($_POST['calldate'])) die("Bad call date");
if (! valid_date($_POST['donedate'])) die("Bad done date");
if (! valid_time($_POST['calltime'])) die("Bad call time");
if (! valid_time($_POST['donetime'])) die("Bad done time");

$unixcall = strtotime($_POST['calltime'] . ' ' . $_POST['calldate']);
$call = date('Y-m-d H:i:s', $unixcall);
$unixdone = strtotime($_POST['donetime'] . ' ' . $_POST['donedate']);
$done = date('Y-m-d H:i:s', $unixdone);
if ($unixdone <= $unixcall) die("Event ends before it begins");

if ($type == 0 || $type == 1 || $type == 2)
{
	if ($repeat != '' && $repeat != 'no')
	{
		if (! valid_date($_POST['until'])) die("Bad repeat-until date");
		$dur = $unixdone - $unixcall;
		if ($repeat == "daily") $interval = '+1 day';
		else if ($repeat == "weekly") $interval = '+1 week';
		else if ($repeat == "biweekly") $interval = '+2 weeks';
		else if ($repeat == "monthly") $interval = '+1 month';
		else if ($repeat == "yearly") $interval = '+1 year';
		else die("Bad repeat mode");
		$end = strtotime('11:59 PM ' . $_POST['until']);
		$cur = $unixcall;
		while ($cur < $end)
		{
			$call = date('Y-m-d H:i:s', $cur);
			$done = date('Y-m-d H:i:s', $cur + $dur);
			$friendly = date('m-d', $cur);
			if ($type == 0) $eventNo = createEvent($_POST['name'], 0, $call, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], 0, 1);
			else $eventNo = createRehearsal($_POST['name'] . ' ' . $friendly, $type, $call, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], ($type == 1 ? 0 : $_POST['section']));
			$cur = strtotime($interval, $cur);
		}
	}
	else if ($type == 0) $eventNo = createEvent($_POST['name'], 0, $call, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], 0, 1);
	else $eventNo = createRehearsal($_POST['name'], $type, $call, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], ($type == 1 ? 0 : $_POST['section']));
}
else if ($type == 3 || $type == 4)
{
	$perftime = $_POST['perftime'];
	if ($perftime == '') $perftime = $_POST['calltime'];
	if (! valid_time($perftime)) die("Bad performance time");
	$unixperform = strtotime($perftime . ' ' . $_POST['calldate']);
	$perform = date('Y-m-d H:i:s', $unixperform);
	if ($unixperform < $unixcall || $unixperform > $unixdone) die("Performance time not between start and end");
	$eventNo = createGig($_POST['name'], ($type == 4 ? true : false), $call, $perform, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], $_POST['uniform'], $_POST['cname'], $_POST['cemail'], $_POST['cphone'], $_POST['price'], isset($_POST['gigcount']), isset($_POST['public']), $_POST['summary'], $_POST['description']);
}
else die("Bad event type");

if ($eventNo < 0) die("Error $eventNo");
if (($type == 3 || $type == 4) && $unixcall > strtotime('now')) eventEmail($eventNo, $type);
echo "$eventNo";
?>
