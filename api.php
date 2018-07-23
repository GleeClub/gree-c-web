<?php
require_once('php/functions.php');

// https://stackoverflow.com/questions/1282909/php-post-array-empty-upon-form-submission
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

function json_error($err)
{
	echo "{ \"status\": \"internal_error\", \"message\": \"JSON encoding error: $err\"}";
}

function utf8ize($mixed) // https://stackoverflow.com/questions/10199017/how-to-solve-json-error-utf8-error-in-php-json-decode
{
	if (is_array($mixed))
		foreach ($mixed as $key => $value)
			$mixed[$key] = utf8ize($value);
	else if (is_string ($mixed))
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

function err($msg, $internal = false)
{
	if ($internal) reply("internal_error", array("message" => $msg));
	reply("error", array("message" => $msg));
}

/*function mkquery($table, $equals = [], $cond = [])
{
	foreach ($equals as $key => $val) $cond[] = "`$key` = '$val'";
	$colstr = [];
	//foreach ($cols as $name => $alias) $colstr[] = "`" . mysql_real_escape_string($name) . "` as `" . mysql_real_escape_string($alias) . "`";
	$sel = "select * from $table";
	if (sizeof($cond) == 0) return $sel;
	return $sel . " where " . implode(" and ", $cond);
}*/

function runquery($qstr, $ints = [], $bools = [])
{
	$query = mysql_query($qstr);
	if (! $query) err("Query failed: " . mysql_error(), true);
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

function hasarg($var)
{
	return isset($_GET[$var]);
}

function arg($var, $ensure = false, $escape = true)
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
}

function priv($perm) # Error if the current user doesn't have the specified permission
{
	global $USER;
	if (! hasPermission($perm)) err("Not authorized");
}

$action = arg("action", true, false);

if ($action == "auth")
{
	$user = postdata("user");
	$pass = postdata("pass");
	if (mysql_num_rows(mysql_query("select * from `member` where email = '$user' and password = md5('$pass')")) != 1) err("Wrong login information");
	reply("ok", array("identity" => cookie_string(postdata("user", true, false)), "choir" => "glee")); // TODO Setting choir
}

if (! $CHOIR) err("Choir is not set");

switch ($action)
{
case "publicevents":
	$sem = $SEMESTER;
	if (hasarg("semester")) $sem = arg("semester");
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
	$starttime = date("Y-m-d H:i:s", postdata("bookingDateOfEventUnix", true, false));
	if (! mysql_query("insert into `gigreq` (`name`, `org`, `cname`, `cphone`, `cemail`, `startTime`, `location`, `comments`, `semester`) values ('" . postdata("bookingNameOfEvent") . "', '" . postdata("bookingOrg") . "', '" . postdata("bookingContactName") . "', '" . postdata("bookingContactPhoneNumber") . "', '" . postdata("bookingContactEmail") . "', '$starttime', '" . postdata("bookingLocationOfEvent") . "', '" . postdata("bookingComments") . "', '$SEMESTER')")) err("Error creating gig request: " . mysql_error());
	$message = "Event: " . postdata("bookingNameOfEvent") . "\n\nAt:\n$starttime\n" . postdata("bookingLocationOfEvent") . "\n\nRequester:\n" . postdata("bookingOrg") . "\n" . postdata("bookingContactName") . "\n" . postdata("bookingContactPhoneNumber") . "\n" . postdata("bookingContactEmail") . "\n\nNotes:\n" . postdata("bookingComments") . "\n\nView gig requests at $BASEURL#gigreqs.\n";
	$choirinfo = mysql_fetch_array(mysql_query("select `admin` from `choir` where `id` = '$CHOIR'"));
	$recipient = $choirinfo["admin"];
	if (! mail($recipient, "New Gig Request", $message)) err("Error sending notification mail");
	reply("ok");
}

if (! $USER) err("Not logged in");

switch ($action)
{
case "attendance":
	$member = $USER;
	$sem = $SEMESTER;
	if (hasarg("member"))
	{
		priv("view-attendance");
		$member = arg("member");
	}
	if (hasarg("semester")) $sem = arg("semester");
	reply("ok", attendance($member, 4, $sem));
case "events":
	$member = $USER;
	$sem = $SEMESTER;
	if (hasarg("member"))
	{
		priv("view-attendance");
		$member = arg("member");
	}
	if (hasarg("semester")) $sem = arg("semester");
	$ret = runquery("select `event`.`eventNo` as `id`, `event`.`name` as `name`, unix_timestamp(`event`.`callTime`) as `call`, unix_timestamp(`gig`.`performanceTime`) as `perform`, unix_timestamp(`event`.`releaseTime`) as `release`, `event`.`points` as `points`, `event`.`comments` as `comments`, `event`.`type` as `type`, `event`.`location` as `location`, `sectionType`.`name` as `section`, `event`.`gigcount` as `gigcount`, `gig`.`uniform` as `uniform`, `gig`.`cemail` as `contact`, `attends`.`shouldAttend` as `shouldAttend`, `attends`.`didAttend` as `didAttend`, `attends`.`confirmed` as `confirmed`, `attends`.`minutesLate` as `late` from `event` natural left join `gig`, `attends`, `sectionType` where `event`.`choir` = '$CHOIR' and `attends`.`memberID` = '$member' and `event`.`semester` = '$sem' and `attends`.`eventNo` = `event`.`eventNo` and `event`.`section` = `sectionType`.`id`", ["id", "call", "perform", "release", "points", "late"], ["gigcount", "shouldAttend", "didAttend", "confirmed"]);
	reply("ok", array("events" => $ret));
case "attendees":
	priv("view-attendance");
	$event = arg("event", true);
	$ret = runquery("select `memberID`, `shouldAttend`, `confirmed` from `attends` where `eventNo` = '$event'", [], ["shouldAttend", "confirmed"]); // FIXME Name formatting
	reply("ok", array("attendees" => $ret));
case "members":
	$ret = runquery("select concat(member.firstName, ' \"', member.prefName, '\" ', member.lastName) as `name`, member.email as `email`, member.phone as `phone`, member.location as `location`, activeSemester.enrollment as `enrollment` from `member`, `activeSemester` where activeSemester.member = member.email and activeSemester.semester = '$SEMESTER'"); // FIXME Name formatting is not correct -- possibly abstract this to the function responsible for the HTML table?
	reply("ok", array("members" => $ret));
case "updateAttendance":
	$event = mysql_real_escape_string(arg("event", true));
	$attend = arg("attend", true);
	$query = mysql_query("select * from `event` where `eventNo` = '$event'");
	if (! $query) err("Error retrieving event: " . mysql_error());
	if (mysql_num_rows($query) != 1) err("Invalid event");
	if ($attend != "1" && $attend != "0") err("Invalid attend value");
	$result = mysql_fetch_array($query);
	if ($result["type"] != "volunteer" && $result["type"] != "tutti") err("Not a gig");
	if ((strtotime($result["callTime"]) - time()) < 86400) err("Responses are closed for this event");
	if ($result["type"] == "tutti" && $attend != "1") err("Try submitting an absence request instead");
	if (! mysql_query("update `attends` set `shouldAttend` = '$attend', confirmed = '1' where `memberID` = '$USER' and `eventNo` = '$event'")) err("Update failed: " . mysql_error(), true);
	reply("ok");
case "songs":
	$ret = [];
	foreach (runquery("select * from `song`", ["id"], ["current"]) as $song)
	{
		$song["links"] = runquery("select `id`, `type`, `name`, `target` from `songLink` where `song` = '" . $song["id"] . "'", ["id"], []);
		$ret[] = $song;
	}
	reply("ok", array("songs" => $ret, "music_dir" => $musicdir));
case "carpools":
case "setlist":
case "member":
	err("Unimplemented", true);
default:
	err("Unknown action \"" . arg("action", false, false) . "\"");
}

err("Missing reply", true);
?>
