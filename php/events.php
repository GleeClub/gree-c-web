<?php
require_once('functions.php');
require_once("$docroot_external/php/lib/google-api-php-client-2.1.3/vendor/autoload.php");

function gcalEvent($ids, $title, $location, $desc, $unixstart, $unixend, $interval)
{
	global $calendar;
	$tz = date_default_timezone_get();
	$cal = get_gcal();

	foreach ($ids as $id)
	{
		$event = new Google_Service_Calendar_Event();
		$event->setId("calev" . $id);
		set_event_fields($event, $title, $desc, $location, $unixstart, $unixend, $tz);
		$event->setAnyoneCanAddSelf(true);
		$event->setGuestsCanSeeOtherGuests(true);
		$cal->events->insert($calendar, $event);
		if ($interval != "")
		{
			$unixstart = strtotime($interval, $unixstart);
			$unixend = strtotime($interval, $unixend);
		}
	}
	/*if ($repeat != '' && $repeat != 'no')
	{
		$spec = "";
		if ($repeat == "daily") $spec = "FREQ=DAILY";
		else if ($repeat == "weekly") $spec = "FREQ=WEEKLY";
		else if ($repeat == "biweekly") $spec = "FREQ=WEEKLY;INTERVAL=2";
		else if ($repeat == "monthly") $spec = "FREQ=MONTHLY";
		else if ($repeat == "yearly") $spec = "FREQ=YEARLY";
		$times = count($ids);
		$recur = "RRULE:$spec;COUNT=$times";
		$event->setRecurrence(array($recur));
	}*/
}

function gcalUpdate($id, $title, $location, $desc, $unixstart, $unixend)
{
	global $calendar;
	$tz = date_default_timezone_get();
	$cal = get_gcal();

	$event = $cal->events->get($calendar, "calev" . $id);
	set_event_fields($event, $title, $desc, $location, $unixstart, $unixend, $tz);
	$cal->events->patch($calendar, "calev" . $id, $event);
}

function eventEmail($eventNo)
{
	GLOBAL $BASEURL, $CHOIR;

	$eventResults = query("select `event`.`name` as `eventName`, `event`.`callTime`, `event`.`releaseTime`, `event`.`comments`, `event`.`location`, `uniform`.`name` as `uniformName`, `eventType`.`name` as `eventTypeName` from `event`, `gig`, `uniform`, `eventType` where `event`.`eventNo` = ? and `gig`.`eventNo` = `event`.`eventNo` and `uniform`.`id` = `gig`.`uniform` and `eventType`.`id` = `event`.`type`", [$eventNo], QONE);
	if (! $eventResults) err("Bad event ID");
	$eventName = $eventResults['eventName'];
	$typeName = $eventResults['eventTypeName'];
	$eventTime = $eventResults['callTime'];
	$eventReleaseTime = $eventResults['releaseTime'];
	$eventComments = $eventResults['comments'];
	$eventLocation = $eventResults['location'];
	$eventUniform = $eventResults['uniformName'];
	$eventTime = strtotime($eventTime);
	$eventTimeDisplay = date("D, M d g:i a", $eventTime);
	$eventReleaseTime = strtotime($eventReleaseTime);
	$eventReleaseTimeDisplay = date("D, M d g:i a", $eventReleaseTime);

	$redirectURL = "$BASEURL/php/fromEmail.php";
	$eventUrl = "$BASEURL/#event:$eventNo";

	if (! $CHOIR) err("Choir not set");
	$row = query("select `name`, `admin`, `list` from `choir` where `id` = ?", [$CHOIR], QONE);
	if (! $row) err("Invalid choir");
	$sender = $row["name"] . " Officers <" . $row['admin'] . ">";
	$recipient = $row["name"] . " <" . $row['list'] . ">";
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

	if ($typeName == "Volunteer Gig") $confirm = "I will attend";
	else if ($typeName == "Tutti Gig") $confirm = "Confirm I will attend";
	$yesform = "<a href='$redirectURL?id=$eventNo&attend=true'>$confirm</a>";
	$noform = "<a href='$redirectURL?id=$eventNo&attend=false'>I will not attend</a>";
	if ($typeName == "Volunteer Gig") $message .= $yesform . "&nbsp;&nbsp;" . $noform;
	else if ($typeName == "Tutti Gig") $message .= $yesform;
	$message .= '</body></html>';
	if (! mail($recipient, $subject, $message, $headers)) err("Failed to send event email", array("recipient" => $recipient, "subject" => $subject, "message" => $message, "headers" => $headers));
}

// Add to event, and everyone's attending
function createEvent($name, $type, $call, $done, $location, $points, $sem, $comments, $gigcount, $section, $defattend)
{
	global $SEMESTER, $CHOIR; // FIXME Why are we using $sem in some places and $SEMESTER in others?  I think this is wrong.
	if (! $CHOIR) err("No choir currently selected");
	$eventNo = query(
		"insert into event (name, choir, callTime, releaseTime, points, comments, type, location, semester, gigcount, defaultAttend) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
		[$name, $CHOIR, $call, $done, $points, $comments, $type, $location, $sem, $gigcount, $defattend], QID
	);

	$shouldAttend = $defattend;
	if (strtotime($call) < strtotime('+48 hours')) $shouldAttend = 0;
	if ($section == 0) query("insert into `attends` (`memberID`, `eventNo`, `shouldAttend`) select `member`, ?, ? from `activeSemester` where `semester` = ? and `choir` = ?", [$eventNo, $shouldAttend, $SEMESTER, $CHOIR]);
	else
	{
		query("update `event` set `section` = ? where `eventNo` = ?", [$section, $eventNo]);
		query("insert into `attends` (`memberID`, `eventNo`) select `member`, ? from `activeSemester` where `section` = ? and `semester` = ? and `choir` = ?", [$eventNo, $section, $SEMESTER, $CHOIR]);
	}
	return $eventNo;
}

// Add to event and gig, and everyone's attending
function createGig($name, $tutti, $call, $perform, $done, $location, $points, $sem, $comments, $uniform, $cname, $cemail, $cphone, $price, $gigcount, $public, $summary, $description, $defattend)
{
	$eventNo = createEvent($name, ($tutti ? 'tutti' : 'volunteer'), $call, $done, $location, $points, $sem, $comments, $gigcount, 0, $defattend);
	query(
		"insert into `gig` (eventNo, performanceTime, uniform, cname, cemail, cphone, price, public, summary, description) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
		[$eventNo, $perform, $uniform, $cname, $cemail, $cphone, $price, $public, $summary, $description]
	);
	return $eventNo;
}

// Add to event, and only one section is attending if defined by $section
function createRehearsal($name, $type, $call, $done, $location, $points, $sem, $comments, $section, $defattend)
{
	if ($type != 'rehearsal' && $type != 'sectional') err("Internal error 1 in createRehearsal; type is $type");
	if ($type == 'rehearsal' && $section != 0) err("Internal error 2 in createRehearsal; type is $type");

	$attend = 0;
	if ($section) $attend = $section;
	return createEvent($name, $type, $call, $done, $location, $points, $sem, $comments, 0, $attend, $defattend);
}

function doNewEvent($params)
{
	$unesc_name = $params["name"];
	$unesc_location = $params["location"];
	$unesc_comments = $params["comments"];
	$eventNo = array();
	$repeat = $params['repeat'];
	$type = $params['type'];
	$defattend = isset($params['defaultAttend']) ? 1 : 0;
	if (! in_array($type, array("volunteer", "tutti", "rehearsal", "sectional", "ombuds", "other"))) err("Bad event type \"$type\"");
	if (! hasEventTypePermission("create", $type)) err("Access denied");

	if (! valid_date($params['calldate'])) err("Bad call date");
	if (! valid_date($params['donedate'])) err("Bad done date");
	if (! valid_time($params['calltime'])) err("Bad call time");
	if (! valid_time($params['donetime'])) err("Bad done time");

	$unixcall = strtotime($params['calltime'] . ' ' . $params['calldate']);
	$call = date('Y-m-d H:i:s', $unixcall);
	$unixdone = strtotime($params['donetime'] . ' ' . $params['donedate']);
	$done = date('Y-m-d H:i:s', $unixdone);
	if ($unixdone <= $unixcall) err("Event ends before it begins");
	$interval = "";

	if ($type == 'volunteer' || $type == 'tutti')
	{
		$perftime = $params['perftime'];
		if ($perftime == '') $perftime = $params['calltime'];
		if (! valid_time($perftime)) err("Bad performance time");
		$unixperform = strtotime($perftime . ' ' . $params['calldate']);
		$perform = date('Y-m-d H:i:s', $unixperform);
		if ($unixperform < $unixcall || $unixperform > $unixdone) err("Performance time not between start and end");
		$eventNo[] = createGig($params['name'], ($type == 'tutti' ? 1 : 0), $call, $perform, $done, $params['location'], $params['points'], $params['semester'], $params['comments'], $params['uniform'], $params['cname'], $params['cemail'], $params['cphone'], $params['price'], isset($params['gigcount']) ? 1 : 0, isset($params['public']) ? 1 : 0, $params['summary'], $params['description'], $defattend);
	}
	else
	{
		if ($repeat != '' && $repeat != 'no')
		{
			if (! valid_date($params['until'])) err("Bad repeat-until date");
			$dur = $unixdone - $unixcall;
			if ($repeat == "daily") $interval = '+1 day';
			else if ($repeat == "weekly") $interval = '+1 week';
			else if ($repeat == "biweekly") $interval = '+2 weeks';
			else if ($repeat == "monthly") $interval = '+1 month';
			else if ($repeat == "yearly") $interval = '+1 year';
			else err("Bad repeat mode");
			$end = strtotime('11:59 PM ' . $params['until']);
			$cur = $unixcall;
			while ($cur < $end)
			{
				$call = date('Y-m-d H:i:s', $cur);
				$done = date('Y-m-d H:i:s', $cur + $dur);
				$friendly = date('m-d', $cur);
				if ($type == 'sectional' || $type == 'rehearsal') $eventNo[] = createRehearsal($params['name'] . ' ' . $friendly, $type, $call, $done, $params['location'], $params['points'], $params['semester'], $params['comments'], ($type == 1 ? 0 : $params['section']), $defattend);
				else $eventNo[] = createEvent($params['name'], $type, $call, $done, $params['location'], $params['points'], $params['semester'], $params['comments'], 0, 0, $defattend);
				$cur = strtotime($interval, $cur);
			}
		}
		else if ($type == 'sectional' || $type == 'rehearsal') $eventNo[] = createRehearsal($params['name'], $type, $call, $done, $params['location'], $params['points'], $params['semester'], $params['comments'], ($type == 'rehearsal' ? 0 : $params['section']), $defattend);
		else $eventNo[] = createEvent($params['name'], $type, $call, $done, $params['location'], $params['points'], $params['semester'], $params['comments'], 0, 0, $defattend);
	}

	if ($eventNo[0] < 0) err("Error " . $eventNo[0]);
	if (($type == 'volunteer' || $type == 'tutti') && $unixcall > strtotime('now')) eventEmail($eventNo[0]);
	gcalEvent($eventNo, $unesc_name, $unesc_location, $unesc_comments, $unixcall, $unixdone, $interval);
	return $eventNo[0];
}

function doEditEvent($params)
{
	if (! isset($params["type"])) err("Missing parameter \"type\"");
	$type = $params['type'];
	$required = ["id", "name", "calldate", "calltime", "donedate", "donetime", "points", "comments", "location", "semester"];
	if ($type == "volunteer" || $type == "tutti") $required = array_merge($required, ["perftime", "uniform", "cname", "cphone", "cemail", "price", "summary", "description"]);
	foreach ($required as $req)
	{
		if (! isset($params[$req])) err("Missing parameter \"$req\"");
	}
	$id = $params['id'];
	$name = $params['name'];
	if (! hasEventPermission("modify", $id)) err("Access denied");

	#if ($type < 0 || $type > 4) err("Bad event type"); # TODO
	if (! valid_date($params['calldate'])) err("Bad call date");
	if (! valid_date($params['donedate'])) err("Bad done date");
	if (! valid_time($params['calltime'])) err("Bad call time");
	if (! valid_time($params['donetime'])) err("Bad done time");
	$perftime = $params['perftime'];
	if ($perftime == '') $perftime = $params['calltime'];
	if (! valid_time($perftime)) err("Bad performance time");
	$unixcall = strtotime($params['calldate'] . ' ' . $params['calltime']);
	$unixperf = strtotime($params['calldate'] . ' ' . $perftime);
	$unixdone = strtotime($params['donedate'] . ' ' . $params['donetime']);
	if ($unixcall > $unixdone) err("Event must start before it ends");
	if (($type == 'volunteer' || $type == 'tutti') && ($unixperf < $unixcall || $unixperf > $unixdone)) err("Performance time must be between start and end");
	$call = date("Y-m-d H:i:s", $unixcall);
	$perf = date("Y-m-d H:i:s", $unixperf);
	$done = date("Y-m-d H:i:s", $unixdone);
	$points = $params['points'];
	$comments = $params['comments'];
	$location = $params['location'];
	$semester = $params['semester'];
	$gigcount = isset($params['gigcount']) ? 1 : 0;
	$uniform = $params['uniform'];
	$cname = $params['cname'];
	$cphone = $params['cphone'];
	$cemail = $params['cemail'];
	$price = $params['price'];
	$public = isset($params['public']) ? 1 : 0;
	$summary = $params['summary'];
	$description = $params['description'];
	$defaultAttend = isset($params['defaultAttend']) ? "true" : "false";

	query(
		"update `event` set `name` = ?, `callTime` = ?, `releaseTime` = ?, `points` = ?, `comments` = ?, `type` = ?, `location` = ?, `semester` = ?, `gigcount` = ?, `defaultAttend` = ? where `eventNo` = ?",
		[$name, $call, $done, $points, $comments, $type, $location, $semester, $gigcount, $defaultAttend, $id]
	);
	if ($type == 'volunteer' || $type == 'tutti') query(
		"update `gig` set `performanceTime` = ?, `uniform` = ?, `cname` = ?, `cphone` = ?, `cemail` = ?, `price` = ?, `public` = ?, `summary` = ?, `description` = ? where `eventNo` = ?",
		[$perf, $uniform, $cname, $cphone, $cemail, $price, $public, $summary, $description, $id]
	);
	gcalUpdate($id, $name, $location, $comments, $unixcall, $unixdone);
	return $id;
}

function doRemoveEvent($id)
{
	global $calendar;
	if (! hasEventPermission("delete", $id)) err("Permission denied");
	query("delete from `event` where `eventNo` = ? limit 1", [$id]);
	$service = get_gcal();
	$service->events->delete($calendar, "calev$id");
}
?>
