<?php
require_once('php/functions.php');
$CHOIR = "glee";
$rest_json = null;
// FIXME Calls to third-party functions e.g. in utility.php generally die() rather than returning JSON error messages.  How do we deal with this?
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type,X-Identity");

function raw_reply($msg, $type = "text/plain", $attach = null)
{
	header("Content-Type: $type");
	if ($attach) header("Content-Disposition: attachment; filename=\"$attach\"");
	echo $msg;
	exit(0);
}

function reply($status, $arr = array())
{
	$arr["status"] = $status;
	json_reply($arr);
}

function fold($str, $w, $sep)
{
	$ret = "";
	while (strlen($str) > $w)
	{
		$ret .= substr($str, 0, $w) . $sep;
		$str = substr($str, $w);
	}
	$ret .= $str;
	return $ret;
}

function rowcast($res, $ints = [], $bools = []) // Ick.  This should be moved into the database.
{
	foreach ($res as &$row)
	{
		foreach ($ints as $field) $row[$field] = (int) $row[$field];
		foreach ($bools as $field) $row[$field] = (bool) $row[$field];
		// if ($row[$field] == null || $row[$field] == 0 || $row[$field] == false || $row[$field] == "null" || $row[$field] == "0" || $row[$field] == "false") $row[$field] = false;
		// else $row[$field] = true;
	}
	return $res;
}

function param($source, $var, $default = null)
{
	if (! isset($source[$var]))
	{
		if (is_null($default)) err("Missing parameter \"$var\"");
		return $default;
	}
	return $source[$var];
}

function get($var, $default = null)
{
	return param($_GET, $var, $default);
}

function post($var, $default = null)
{
	global $rest_json;
	//https://stackoverflow.com/questions/1282909/php-post-array-empty-upon-form-submission
	if ($rest_json === null)
	{
		$rest_json = json_decode(file_get_contents("php://input"), true);
		if ($rest_json === null) err("Request payload was not valid JSON");
	}
	return param($rest_json, $var, $default);
}

function priv($perm) # Error if the current user doesn't have the specified permission
{
	if (! hasPermission($perm)) err("Not authorized");
}

$action = param($_GET, "action");

if ($action == "auth")
{
	if (query("select * from `member` where email = ? and password = md5(?)", [post("user"), post("pass")], QCOUNT) != 1) err("Wrong login information");
	reply("ok", array("identity" => cookie_string(post("user")), "choir" => "glee")); // TODO Setting choir
}

if (! $CHOIR) err("Choir is not set");
require_once("php/choir/$CHOIR/base.php");

switch ($action)
{
case "publicevents":
	$sem = get("semester", $SEMESTER);
	$ret = query("select `event`.`eventNo` as `id`, `event`.`name` as `name`, unix_timestamp(`gig`.`performanceTime`) as `time`, `event`.`location` as `location`, `gig`.`summary` as `summary`, `gig`.`description` as `description` from `event`, `gig` where `event`.`choir` = ? and `event`.`semester` = ? and `event`.`eventNo` = `gig`.`eventNo` and `gig`.`public` = 1 and `gig`.`performanceTime` > current_timestamp", [$CHOIR, $sem], QALL);
	reply("ok", array("events" => $ret));
case "publicsongs":
	$ret = [];
	foreach (query("select `id`, `title` from `song` where `choir` = ?", [$CHOIR], QALL) as $song)
	{
		$song["links"] = query("select `id`, `name`, `target` as `ytid` from `songLink` where `song` = ? and `type` = 'video'", [$song["id"]], QALL);
		$ret[] = $song;
	}
	reply("ok", array("songs" => $ret));
case "gigreq":
	$starttime = date("Y-m-d H:i:s", post("bookingDateOfEventUnix"));
	$phone = preg_replace("/[^0-9]/", "", post("bookingContactPhoneNumber"));
	query(
		"insert into `gigreq` (`name`, `org`, `cname`, `cphone`, `cemail`, `startTime`, `location`, `comments`, `semester`) values (?, ?, ?, ?, ?, ?, ?, ?, ?)",
		[post("bookingNameOfEvent"), post("bookingOrg"), post("bookingContactName"), $phone, post("bookingContactEmail"), $starttime, post("bookingLocationOfEvent"), post("bookingComments"), $SEMESTER]

	);
	$message = "Event: " . post("bookingNameOfEvent") . "\n\nAt:\n$starttime\n" . post("bookingLocationOfEvent") . "\n\nRequester:\n" . post("bookingOrg") . "\n" . post("bookingContactName") . "\n" . $phone . "\n" . post("bookingContactEmail") . "\n\nNotes:\n" . post("bookingComments") . "\n\nView gig requests at $BASEURL#gigreqs.\n";
	$choirinfo = query("select `admin` from `choir` where `id` = ?", [$CHOIR], QONE);
	if (! $choirinfo) err("Invalid choir");
	$recipient = $choirinfo["admin"];
	if (! mail($recipient, "New Gig Request", $message)) err("Error sending notification mail", "Call to mail() failed in gig request via API");
	reply("ok");
case "calendar":
	$id = get("event");
	$event = query("select unix_timestamp(`gig`.`performanceTime`) as `start`, unix_timestamp(`event`.`releaseTime`) as `end`, `event`.`name` as `summary`, `gig`.`summary` as `description`, `event`.`location` as `location` from `event`, `gig` where `event`.`eventNo` = $id and `gig`.`eventNo` = ? and `event`.`choir` = ? and `gig`.`public` = 1", [$id, $CHOIR], QONE);
	if (! $event) err("The event you requested does not appear to exist");
	$timefmt = "Ymd\\THis\\Z";
	$now = gmdate($timefmt);
	$cal = array("UID" => "$now@$domain", "DTSTAMP" => "$now", "DTSTART" => gmdate($timefmt, $event["start"]), "DTEND" => gmdate($timefmt, $event["end"]), "SUMMARY" => $event["summary"], "DESCRIPTION" => $event["description"], "LOCATION" => $event["location"]);
	$ret = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\nBEGIN:VEVENT\r\n";
	foreach ($cal as $k => $v) $ret .= fold("$k:$v", 72, "\r\n ") . "\r\n";
	$ret .= "END:VEVENT\r\nEND:VCALENDAR\r\n";
	raw_reply($ret, "text/calendar", "event.ics");
}

if (! $USER) err("Not logged in");

switch ($action)
{
case "attendance":
	$sem = get("semester", $SEMESTER);
	$member = get("member", $USER);
	if ($member != $USER) priv("view-attendance");
	reply("ok", attendance($member, $sem));
case "events":
	$sem = get("semester", $SEMESTER);
	$member = get("member", $USER);
	if ($member != $USER) priv("view-attendance");
	$ret = query("select `event`.`eventNo` as `id`, `event`.`name` as `name`, unix_timestamp(`event`.`callTime`) as `call`, unix_timestamp(`gig`.`performanceTime`) as `perform`, unix_timestamp(`event`.`releaseTime`) as `release`, `event`.`points` as `points`, `event`.`comments` as `comments`, `event`.`type` as `type`, `event`.`location` as `location`, `sectionType`.`name` as `section`, `event`.`gigcount` as `gigcount`, `gig`.`uniform` as `uniform`, `gig`.`cemail` as `contact`, `attends`.`shouldAttend` as `shouldAttend`, `attends`.`didAttend` as `didAttend`, `attends`.`confirmed` as `confirmed`, `attends`.`minutesLate` as `late` from `event` natural left join `gig`, `attends`, `sectionType` where `event`.`choir` = ? and `attends`.`memberID` = ? and `event`.`semester` = ? and `attends`.`eventNo` = `event`.`eventNo` and `event`.`section` = `sectionType`.`id`", [$CHOIR, $member, $sem], QALL);
	$ret = rowcast($ret, [], ["gigcount", "shouldAttend", "didAttend", "confirmed"]);
	reply("ok", array("events" => $ret));
case "attendees":
	priv("view-attendance");
	$event = get("event");
	$ret = query("select `memberID`, `shouldAttend`, `confirmed` from `attends` where `eventNo` = ?", [$event], QALL);
	$ret = rowcast($ret, [], ["shouldAttend", "confirmed"]);
	reply("ok", array("attendees" => $ret));
case "members":
	//$ret = query("select concat(member.firstName, ' ', (case when member.prefName = '' then '' else concat('\"', member.prefName, '\" ') end), member.lastName) as `name`, member.email as `email`, member.phone as `phone`, member.location as `location`, activeSemester.enrollment as `enrollment` from `member`, `activeSemester` where activeSemester.member = member.email and activeSemester.semester = ?", [$SEMESTER], QALL);
	$ret = [];
	foreach (listMembers() as $email => $name) $ret[] = memberInfo($email);
	reply("ok", array("members" => $ret));
case "updateAttendance":
	$event = get("event");
	$attend = get("attend");
	if ($attend != "1" && $attend != "0") err("Invalid attend value");
	$result = query("select * from `event` where `eventNo` = ?", [$event], QONE);
	if (! $result) err("Invalid event");
	if ($result["type"] != "volunteer" && $result["type"] != "tutti") err("Not a gig");
	if ((strtotime($result["callTime"]) - time()) < 86400) err("Responses are closed for this event");
	if ($result["type"] == "tutti" && $attend != "1") err("Try submitting an absence request instead");
	query("update `attends` set `shouldAttend` = ?, confirmed = '1' where `memberID` = ? and `eventNo` = ?", [$attend, $USER, $event]);
	reply("ok");
case "songs":
	$ret = [];
	foreach (query("select * from `song` where `choir` = ?", [$CHOIR], QALL) as $song)
	{
		$song["current"] = (bool) $song["current"];
		$song["links"] = query("select `id`, `type`, `name`, `target` from `songLink` where `song` = ?", [$song["id"]], QALL);
		$ret[] = $song;
	}
	reply("ok", array("songs" => $ret, "music_dir" => $musicdir));
case "member":
	$ret = memberInfo(get("member"));
	reply("ok", array("profile" => $ret));
case "carpools":
	$res = [];
	foreach (query("select `carpool`.`carpoolID` as `id`, `carpool`.`driver` as `driver`, `ridesin`.`memberID` as `passenger` from `carpool`, `ridesin` where `carpool`.`eventNo` = ? and `ridesin`.`carpoolID` = `carpool`.`carpoolID` and `carpool`.`driver` != `ridesin`.`memberID` order by `carpool`.`carpoolID` asc", [get("event")], QALL) as $carpool)
	{
		if (! array_key_exists($carpool["id"], $res)) $res[$carpool["id"]] = array("id" => $carpool["id"], "driver" => $carpool["driver"], "passengers" => []);
		$res[$carpool["id"]]["passengers"][] = $carpool["passenger"];
	}
	$ret = [];
	foreach ($res as $carpool) $ret[] = $carpool;
	reply("ok", array("carpools" => $ret));
case "updateCarpools":
case "setlist":
	err("Unimplemented");
default:
	err("Unknown action \"$action\"");
}

err("", "Control flow reached the end of api.php without a reply() call");
?>
