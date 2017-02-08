<?php
require_once('functions.php');

function eventEmail($eventNo, $type)
{
	GLOBAL $BASEURL, $CHOIR;
	$sql = "select * from `eventType` where `id` = '$type'";
	$eventType  =  mysql_fetch_array(mysql_query($sql));
	$typeName = $eventType['name'];
	
	$eventResults = mysql_fetch_array(mysql_query("SELECT * from `event` where `eventNo` = '$eventNo'"));
	$eventName = $eventResults['name'];
	$eventType = $eventResults['type'];
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
	
	$typeResults = mysql_fetch_array(mysql_query("SELECT * from `eventType` where `id` = '$eventType`"));
	$eventType = $typeResults['name'];

	$redirectURL = "$BASEURL/php/fromEmail.php";
	$eventUrl = "$BASEURL/#event:$eventNo";

	if (! $CHOIR) die("Choir not set");
	$row = mysql_fetch_array(mysql_query("select `admin`, `list` from `choir` where `id` = '$CHOIR'"));
	$sender = $row['admin'];
	$recipient = $row['list'];
	//$recipient = "Matthew Schauer <awesome@gatech.edu>";
	$choirname = choirname($CHOIR);
	$subject = "New $choirname Event: $eventName";
	$headers = "Content-type:text/html;\n" .
		"Reply-To: $sender\n" .
		"From: $sender\n" .
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
	global $SEMESTER, $CHOIR;
	if (! $CHOIR) die("No choir currently selected");
	if (! mysql_query("insert into event (name, choir, callTime, releaseTime, points, comments, type, location, semester, gigcount) values ('$name', '$CHOIR', '$call', '$done', '$points', '$comments', '$type', '$location', '$sem', '$gigcount')")) die("Failed to create event: " . mysql_error());
	$eventNo = mysql_insert_id();

	if ($section >= 0 && strtotime($call) > strtotime('now')) // -1 for nobody to attend, 0 for everyone to attend
	{
		$shouldAttend = 1;
		if (strtotime($call) < strtotime('+48 hours')) $shouldAttend = 0;
		if ($section == 0) { if (! mysql_query("insert into `attends` (`memberID`, `eventNo`, `shouldAttend`) select `member`, '$eventNo', '$shouldAttend' from `activeSemester` where `semester` = '$SEMESTER' and `choir` = '$CHOIR'")) die("Failed to insert attends relations for event: " . mysql_error()); }
		else
		{
			if (! mysql_query("update `event` set `section` = '$section' where `eventNo` = '$eventNo'")) die("Failed to set section: " . mysql_error());
			if (! mysql_query("insert into `attends` (`memberID`, `eventNo`) select `member`, '$eventNo' from `activeSemester` where `section` = '$section' and `semester` = '$SEMESTER' and `choir` = '$CHOIR'")) die("Failed to create attends relation for sectional: " . mysql_error());
		}
	}
	return $eventNo;
}

// Add to event and gig, and everyone's attending
function createGig($name, $tutti, $call, $perform, $done, $location, $points, $sem, $comments, $uniform, $cname, $cemail, $cphone, $price, $gigcount, $public, $summary, $description)
{
	$eventNo = createEvent($name, ($tutti ? 'tutti' : 'volunteer'), $call, $done, $location, $points, $sem, $comments, $gigcount, 0);

	$publicval = $public ? 1 : 0;
	if (! mysql_query("insert into `gig` (eventNo, performanceTime, uniform, cname, cemail, cphone, price, public, summary, description) values ('$eventNo', '$perform', '$uniform', '$cname', '$cemail', '$cphone', '$price', '$publicval', '$summary', '$description')")) die("Failed to create gig: " . mysql_error());
	return $eventNo;
}

// Add to event, and only one section is attending if defined by $section
function createRehearsal($name, $type, $call, $done, $location, $points, $sem, $comments, $section)
{
	if ($type != 'rehearsal' && $type != 'sectional') die("Internal error 1 in createRehearsal; type is $type");
	if ($type == 'rehearsal' && $section != 0) die("Internal error 2 in createRehearsal; type is $type");

	$attend = 0;
	if ($type == 'sectional') $attend = -1;
	if ($section) $attend = $section;
	return createEvent($name, $type, $call, $done, $location, $points, $sem, $comments, 0, $attend);
}

foreach ($_POST as &$value) $value = mysql_real_escape_string($value);
$eventNo = -1;
$repeat = $_POST['repeat'];
$type = $_POST['type'];
if (! in_array($type, array("volunteer", "tutti", "rehearsal", "sectional", "ombuds", "other"))) die("Bad event type \"$type\"");
if (! canEditEvents($USER, $type)) die("Access denied");

if (! valid_date($_POST['calldate'])) die("Bad call date");
if (! valid_date($_POST['donedate'])) die("Bad done date");
if (! valid_time($_POST['calltime'])) die("Bad call time");
if (! valid_time($_POST['donetime'])) die("Bad done time");

$unixcall = strtotime($_POST['calltime'] . ' ' . $_POST['calldate']);
$call = date('Y-m-d H:i:s', $unixcall);
$unixdone = strtotime($_POST['donetime'] . ' ' . $_POST['donedate']);
$done = date('Y-m-d H:i:s', $unixdone);
if ($unixdone <= $unixcall) die("Event ends before it begins");

if ($type == 'volunteer' || $type == 'tutti')
{
	$perftime = $_POST['perftime'];
	if ($perftime == '') $perftime = $_POST['calltime'];
	if (! valid_time($perftime)) die("Bad performance time");
	$unixperform = strtotime($perftime . ' ' . $_POST['calldate']);
	$perform = date('Y-m-d H:i:s', $unixperform);
	if ($unixperform < $unixcall || $unixperform > $unixdone) die("Performance time not between start and end");
	$eventNo = createGig($_POST['name'], ($type == 'tutti' ? true : false), $call, $perform, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], $_POST['uniform'], $_POST['cname'], $_POST['cemail'], $_POST['cphone'], $_POST['price'], isset($_POST['gigcount']), isset($_POST['public']), $_POST['summary'], $_POST['description']);
}
else
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
			if ($type == 'sectional' || $type == 'rehearsal') $eventNo = createRehearsal($_POST['name'] . ' ' . $friendly, $type, $call, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], ($type == 1 ? 0 : $_POST['section']));
			else $eventNo = createEvent($_POST['name'], $type, $call, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], 0, 1);
			$cur = strtotime($interval, $cur);
		}
	}
	else if ($type == 'sectional' || $type == 'rehearsal') $eventNo = createRehearsal($_POST['name'], $type, $call, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], ($type == 'rehearsal' ? 0 : $_POST['section']));
	else $eventNo = createEvent($_POST['name'], $type, $call, $done, $_POST['location'], $_POST['points'], $_POST['semester'], $_POST['comments'], 0, 1);
}

if ($eventNo < 0) die("Error $eventNo");
if (($type == 'volunteer' || $type == 'tutti') && $unixcall > strtotime('now')) eventEmail($eventNo, $type);
echo "$eventNo";
?>
