<?php
require_once('php/functions.php');

// https://stackoverflow.com/questions/1282909/php-post-array-empty-upon-form-submission
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

function raw_reply($msg, $type = "text/plain", $attach = null)
{
	header("Content-Type: $type");
	if ($attach) header("Content-Disposition: attachment; filename=\"$attach\"");
	echo $msg;
	exit(0);
}

function json_error($err)
{
	echo "{ \"status\": \"internal_error\", \"message\": \"JSON encoding error: $err\"}";
}

function utf8ize($mixed) // https://stackoverflow.com/questions/10199017/how-to-solve-json-error-utf8-error-in-php-json-decode
{
	if (is_array($mixed))
		foreach ($mixed as $key => $value)
			$mixed[$key] = utf8ize($value);
	else if (is_string($mixed))
		return utf8_encode($mixed);
	return $mixed;
}

function reply($status, $arr = array())
{
	$arr["status"] = $status;
	$ret = json_encode(utf8ize($arr));
	switch (json_last_error())
	{
		case JSON_ERROR_NONE: echo($ret); break;
		case JSON_ERROR_DEPTH: json_error("DEPTH"); break;
		case JSON_ERROR_STATE_MISMATCH: json_error("STATE_MISMATCH"); break;
		case JSON_ERROR_CTRL_CHAR: json_error("CTRL_CHAR"); break;
		case JSON_ERROR_SYNTAX: json_error("SYNTAX"); break;
		case JSON_ERROR_UTF8: json_error("UTF8"); break;
		case JSON_ERROR_RECURSION: json_error("RECURSION"); break;
		case JSON_ERROR_INF_OR_NAN: json_error("INF_OR_NAN"); break;
		case JSON_ERROR_UNSUPPORTED_TYPE: json_error("UNSUPPORTED_TYPE"); break;
		case JSON_ERROR_INVALID_PROPERTY_NAME: json_error("INVALID_PROPERTY_NAME"); break;
		case JSON_ERROR_UTF16: json_error("UTF16"); break;
		default: json_error("Unknown"); break;
	}
	exit(0);
}

function err($msg)
{
	reply("error", array("message" => $msg));
}

function internal_err($msg)
{
	reply("internal_error", array("message" => $msg));
}

function runquery($qstr, $ints = [], $bools = [])
{
	$query = mysql_query($qstr);
	if (! $query) internal_err("Query failed: " . mysql_error());
	$ret = [];
	while ($row = mysql_fetch_array($query, MYSQL_ASSOC))
	{
		foreach ($ints as $field) $row[$field] = intval($row[$field]);
		foreach ($bools as $field)
		{
			if ($row[$field] == null || $row[$field] == 0 || $row[$field] == false || $row[$field] == "null" || $row[$field] == "0" || $row[$field] == "false") $row[$field] = false;
			else $row[$field] = true;
		}
		$ret[] = $row;
	}
	return $ret;
}

/*function arg($var, $ensure = false, $escape = true)
{
	if (! isset($_GET[$var]))
	{
		if ($ensure) err("Missing argument \"$var\"");
		return null;
	}
	if ($escape) return mysql_real_escape_string($_GET[$var]);
	return $_GET[$var];
}

function postdata($var, $ensure = true, $escape = true)
{
	$rest_json = file_get_contents("php://input");
	$_POST = json_decode($rest_json, true);
	if (! isset($_POST[$var]))
	{
		if ($ensure) err("Missing argument \"$var\"");
		return null;
	}
	if ($escape) return mysql_real_escape_string($_POST[$var]);
	return $_POST[$var];
}*/

function param($source, $var, $default = null)
{
	if (! isset($source[$var]))
	{
		if (is_null($default)) err("Missing argument \"$var\"");
		return $default;
	}
	return $source[$var];
}

function get($var, $default = null)
{
	return mysql_real_escape_string(param($_GET, $var, $default));
}

function post($var, $default = null)
{
	return mysql_real_escape_string(param($_POST, $var, $default));
}

function priv($perm) # Error if the current user doesn't have the specified permission
{
	//global $USER;
	if (! hasPermission($perm)) err("Not authorized");
}

$action = param($_GET, "action");

if ($action == "auth")
{
	$user = post("user");
	$pass = post("pass");
	if (mysql_num_rows(mysql_query("select * from `member` where email = '$user' and password = md5('$pass')")) != 1) err("Wrong login information");
	reply("ok", array("identity" => cookie_string(param($_POST, "user")), "choir" => "glee")); // TODO Setting choir
}

if (! $CHOIR) err("Choir is not set");

switch ($action)
{
case "publicevents":
	$sem = get("semester", $SEMESTER);
	$ret = runquery("select `event`.`eventNo` as `id`, `event`.`name` as `name`, unix_timestamp(`gig`.`performanceTime`) as `time`, `event`.`location` as `location`, `gig`.`summary` as `summary`, `gig`.`description` as `description` from `event`, `gig` where `event`.`choir` = '$CHOIR' and `event`.`semester` = '$sem' and `event`.`eventNo` = `gig`.`eventNo` and `gig`.`public` = 1", ["id", "time"]);
	reply("ok", array("events" => $ret));
case "publicsongs":
	$ret = [];
	foreach (runquery("select `id`, `title` from `song`", ["id"]) as $song)
	{
		$song["links"] = runquery("select `id`, `name`, `target` as `ytid` from `songLink` where `song` = '" . $song["id"] . "' and `type` = 'video'", ["id"], []);
		$ret[] = $song;
	}
	reply("ok", array("songs" => $ret));
case "gigreq":
	$starttime = date("Y-m-d H:i:s", post("bookingDateOfEventUnix"));
	if (! mysql_query("insert into `gigreq` (`name`, `org`, `cname`, `cphone`, `cemail`, `startTime`, `location`, `comments`, `semester`) values ('" . post("bookingNameOfEvent") . "', '" . post("bookingOrg") . "', '" . post("bookingContactName") . "', '" . post("bookingContactPhoneNumber") . "', '" . post("bookingContactEmail") . "', '$starttime', '" . post("bookingLocationOfEvent") . "', '" . post("bookingComments") . "', '$SEMESTER')")) err("Error creating gig request: " . mysql_error());
	$message = "Event: " . post("bookingNameOfEvent") . "\n\nAt:\n$starttime\n" . post("bookingLocationOfEvent") . "\n\nRequester:\n" . post("bookingOrg") . "\n" . post("bookingContactName") . "\n" . post("bookingContactPhoneNumber") . "\n" . post("bookingContactEmail") . "\n\nNotes:\n" . post("bookingComments") . "\n\nView gig requests at $BASEURL#gigreqs.\n";
	$choirinfo = mysql_fetch_array(mysql_query("select `admin` from `choir` where `id` = '$CHOIR'"));
	$recipient = $choirinfo["admin"];
	if (! mail($recipient, "New Gig Request", $message)) internal_err("Error sending notification mail");
	reply("ok");
case "calendar":
	raw_reply("hello", "text/calendar", "event.ics"); // TODO
}

if (! $USER) err("Not logged in");

switch ($action)
{
case "attendance":
	$sem = get("semester", $SEMESTER);
	$member = get("member", $USER);
	if ($member != $USER) priv("view-attendance");
	reply("ok", attendance($member, 4, $sem));
case "events":
	$sem = get("semester", $SEMESTER);
	$member = get("member", $USER);
	if ($member != $USER) priv("view-attendance");
	$ret = runquery("select `event`.`eventNo` as `id`, `event`.`name` as `name`, unix_timestamp(`event`.`callTime`) as `call`, unix_timestamp(`gig`.`performanceTime`) as `perform`, unix_timestamp(`event`.`releaseTime`) as `release`, `event`.`points` as `points`, `event`.`comments` as `comments`, `event`.`type` as `type`, `event`.`location` as `location`, `sectionType`.`name` as `section`, `event`.`gigcount` as `gigcount`, `gig`.`uniform` as `uniform`, `gig`.`cemail` as `contact`, `attends`.`shouldAttend` as `shouldAttend`, `attends`.`didAttend` as `didAttend`, `attends`.`confirmed` as `confirmed`, `attends`.`minutesLate` as `late` from `event` natural left join `gig`, `attends`, `sectionType` where `event`.`choir` = '$CHOIR' and `attends`.`memberID` = '$member' and `event`.`semester` = '$sem' and `attends`.`eventNo` = `event`.`eventNo` and `event`.`section` = `sectionType`.`id`", ["id", "call", "perform", "release", "points", "late"], ["gigcount", "shouldAttend", "didAttend", "confirmed"]);
	reply("ok", array("events" => $ret));
case "attendees":
	priv("view-attendance");
	$event = get("event");
	$ret = runquery("select `memberID`, `shouldAttend`, `confirmed` from `attends` where `eventNo` = '$event'", [], ["shouldAttend", "confirmed"]); // FIXME Name formatting
	reply("ok", array("attendees" => $ret));
case "members":
	$ret = runquery("select concat(member.firstName, ' ', (case when member.prefName = '' then '' else concat('\"', member.prefName, '\" ') end), member.lastName) as `name`, member.email as `email`, member.phone as `phone`, member.location as `location`, activeSemester.enrollment as `enrollment` from `member`, `activeSemester` where activeSemester.member = member.email and activeSemester.semester = '$SEMESTER'");
	reply("ok", array("members" => $ret));
case "updateAttendance":
	$event = get("event");
	$attend = get("attend");
	if ($attend != "1" && $attend != "0") err("Invalid attend value");
	$query = mysql_query("select * from `event` where `eventNo` = '$event'");
	if (! $query) internal_err("Error retrieving event: " . mysql_error());
	if (mysql_num_rows($query) != 1) err("Invalid event");
	$result = mysql_fetch_array($query);
	if ($result["type"] != "volunteer" && $result["type"] != "tutti") err("Not a gig");
	if ((strtotime($result["callTime"]) - time()) < 86400) err("Responses are closed for this event");
	if ($result["type"] == "tutti" && $attend != "1") err("Try submitting an absence request instead");
	if (! mysql_query("update `attends` set `shouldAttend` = '$attend', confirmed = '1' where `memberID` = '$USER' and `eventNo` = '$event'")) internal_err("Update failed: " . mysql_error());
	reply("ok");
case "songs":
	$ret = [];
	foreach (runquery("select * from `song`", ["id"], ["current"]) as $song)
	{
		$song["links"] = runquery("select `id`, `type`, `name`, `target` from `songLink` where `song` = '" . $song["id"] . "'", ["id"], []);
		$ret[] = $song;
	}
	reply("ok", array("songs" => $ret, "music_dir" => $musicdir));
case "member":
	$ret = [];
	$member = get("member");
	$query = mysql_query("select * from `member` where `email` = '$member'");
	if (mysql_num_rows($query) != 1) err("Invalid member");
	$info = mysql_fetch_array($query);
	$ret["positions"] = positions($member);
	$ret["name"] = completeNameFromEmail($member);
	if ($info["about"] == "") $ret["quote"] = "I don't have a quote";
	else $ret["quote"] = $info["about"];
	if ($info["picture"] == "") $ret["picture"] = "http://lorempixel.com/g/256/256";
	else $ret["picture"] = $info["picture"];
	$ret["email"] = $info["email"];
	$ret["phone"] = $info["phone"];
	$ret["section"] = sectionFromEmail($member, 1);
	if ($info["passengers"] == 0) $ret["car"] = "No";
	else $ret["car"] = $info["passengers"] . " passengers";
	$ret["major"] = $info["major"];
	$ret["techYear"] = $info["techYear"];
	if (hasPermission("view-user-private-info"))
	{
		$query2 = mysql_query("select `semester`.`semester` from `activeSemester`, `semester` where `activeSemester`.`member` = '$member' and `activeSemester`.`semester` = `semester`.`semester` order by `semester`.`beginning` desc");
		$semesters = [];
		while ($row = mysql_fetch_array($query2)) $semesters[] = $row["semester"];
		$ret["activeSemesters"] = $semesters;
		$ret["enrollment"] = ""; // TODO
		$ret["gigs"] = attendance($member, 3);
		$ret["score"] = attendance($member, 0);
	}
	if (hasPermission("view-transactions"))
	{
		$ret["balance"] = intval(balance($member));
		$dues = mysql_fetch_array(mysql_query("select sum(`amount`) as `balance` from `transaction` where `memberID` = '" . $member['email'] . "' and `type` = 'dues' and `semester` = '$SEMESTER'"))["balance"];
		if ($dues == "") $ret["dues"] = 0;
		else $ret["dues"] = $dues;
	}
	reply("ok", array("profile" => $ret));
case "carpools":
case "setlist":
	internal_err("Unimplemented");
default:
	err("Unknown action \"$action\"");
}

internal_err("Missing reply");
?>
