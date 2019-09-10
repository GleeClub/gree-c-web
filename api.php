<?php
require_once('php/functions.php');
require_once('php/events.php');
$CHOIR = "glee";
$rest_json = null;
// FIXME Calls to third-party functions e.g. in utility.php generally die() rather than returning JSON error messages.  How do we deal with this?
header("Access-Control-Allow-Origin: *"); // TODO Remove access control headers eventually
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

function keyarray($arr, $key)
{
	$ret = [];
	foreach ($arr as $item)
	{
		$k = $item[$key];
		unset($item[$key]);
		if (count($item) > 1) $ret[$k] = $item;
		else if (count($item) == 1) $ret[$k] = $item[array_keys($item)[0]];
		else $ret[] = $k;
	}
	return $ret;
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
		if ($rest_json === null) err("Corrupt request payload");
	}
	return param($rest_json, $var, $default);
}

function priv($perm) # Error if the current user doesn't have the specified permission
{
	if (! hasPermission($perm)) err("Not authorized");
}

$action = param($_GET, "action");

switch ($action)
{
case "auth":
	if (query("select * from `member` where email = ? and password = md5(?)", [post("user"), post("pass")], QCOUNT) != 1) err("Wrong login information");
	reply("ok", array("identity" => encrypt2(post("user")), "choir" => "glee")); // TODO Setting choir
case "user": // TODO Document
	$ret = array("choir" => null, "id" => null, "name" => "Signed Out", "authenticated" => false, "permissions" => []);
	if ($CHOIR) $ret["choir"] = $CHOIR;
	if ($USER && query("select * from `member` where `email` = ?", [$USER], QONE))
	{
		$ret["authenticated"] = true;
		$ret["id"] = $USER;
		$ret["name"] = memberName($USER, "all");
		$ret["enrollment"] = "inactive";
		$ret["section"] = 0;
		if ($CHOIR)
		{
			$ret["permissions"] = permissions();
			$enrollment = query("select * from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$USER, $SEMESTER, $CHOIR], QONE);
			if ($enrollment)
			{
				$ret["enrollment"] = $enrollment["enrollment"];
				$ret["section"] = $enrollment["section"];
			}
		}
	}
	reply("ok", $ret);
case "forgot": // TODO Document
	forgotPasswordEmail(get("email"));
	reply("ok");
}

if (! $CHOIR) err("Choir is not set");
require_once("php/choir/$CHOIR/base.php");

switch ($action)
{
case "info": // TODO Document
	$ret = query("select * from `variables`", [], QONE);
	$ret["sections"] = keyarray(query("select `id`, `name` from `sectionType` where `choir` = ?", [$CHOIR], QALL), "id");
	$ret["sections"]["0"] = "None";
	$ret["eventTypes"] = keyarray(query("select * from `eventType` order by `weight` asc", [], QALL), "id");
	$ret["uniforms"] = keyarray(query("select `id`, `name`, `description`, `color` from `uniform` where `choir` = ?", [$CHOIR], QALL), "id");
	$ret["documents"] = keyarray(query("select `name`, `url` from `gdocs` where `choir` = ?", [$CHOIR], QALL), "name");
	reply("ok", array("info" => $ret));
case "publicEvents":
	$sem = get("semester", $SEMESTER);
	$ret = query("select `event`.`eventNo` as `id`, `event`.`name` as `name`, unix_timestamp(`gig`.`performanceTime`) as `time`, `event`.`location` as `location`, `gig`.`summary` as `summary`, `gig`.`description` as `description` from `event`, `gig` where `event`.`choir` = ? and `event`.`semester` = ? and `event`.`eventNo` = `gig`.`eventNo` and `gig`.`public` = 1 and `event`.`releaseTime` > current_timestamp()", [$CHOIR, $sem], QALL);
	reply("ok", array("events" => $ret));
case "publicSongs":
	$ret = [];
	foreach (query("select `id`, `title` from `song` where `choir` = ?", [$CHOIR], QALL) as $song)
	{
		$song["links"] = query("select `id`, `name`, `target` as `ytid` from `songLink` where `song` = ? and `type` = 'video'", [$song["id"]], QALL);
		$ret[] = $song;
	}
	reply("ok", array("songs" => $ret));
case "gigRequest":
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
	$event = query("select unix_timestamp(`gig`.`performanceTime`) as `start`, unix_timestamp(`event`.`releaseTime`) as `end`, `event`.`name` as `summary`, `gig`.`summary` as `description`, `event`.`location` as `location` from `event`, `gig` where `event`.`eventNo` = `gig`.`eventNo` and `gig`.`eventNo` = ? and `event`.`choir` = ? and `gig`.`public` = 1", [$id, $CHOIR], QONE);
	if (! $event) err("The event you requested does not appear to exist");
	$timefmt = "Ymd\\THis\\Z";
	$now = gmdate($timefmt);
	$cal = array("UID" => "$now@$domain", "DTSTAMP" => "$now", "DTSTART" => gmdate($timefmt, $event["start"]), "DTEND" => gmdate($timefmt, $event["end"]), "SUMMARY" => $event["summary"], "DESCRIPTION" => $event["description"], "LOCATION" => $event["location"]);
	$ret = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\nBEGIN:VEVENT\r\n";
	foreach ($cal as $k => $v) $ret .= fold("$k:$v", 72, "\r\n ") . "\r\n";
	$ret .= "END:VEVENT\r\nEND:VCALENDAR\r\n";
	raw_reply($ret, "text/calendar", "event.ics");
case "updateProfile":
	$params = post("profile");
	$params["choir"] = $CHOIR;
	doEditProfile(null, $params);
	reply("ok");
}

if (! $USER) err("Not logged in");

switch ($action)
{
case "events":
	$sem = get("semester", $SEMESTER);
	$member = get("member", $USER);
	if ($member != $USER) priv("view-attendance");
	$events = query("select `event`.`eventNo` as `id`, `event`.`name` as `name`, unix_timestamp(`event`.`callTime`) as `call`, unix_timestamp(`gig`.`performanceTime`) as `perform`, unix_timestamp(`event`.`releaseTime`) as `release`, `event`.`points` as `points`, `event`.`comments` as `comments`, `event`.`type` as `type`, `event`.`location` as `location`, `sectionType`.`name` as `section`, `event`.`gigcount` as `gigCount`, `gig`.`uniform` as `uniform`, `gig`.`cemail` as `contact`, `attends`.`shouldAttend` as `shouldAttend`, `attends`.`didAttend` as `didAttend`, `attends`.`confirmed` as `confirmed`, `attends`.`minutesLate` as `late` from `event` natural left join `gig`, `attends`, `sectionType` where `event`.`choir` = ? and `attends`.`memberID` = ? and `event`.`semester` = ? and `attends`.`eventNo` = `event`.`eventNo` and `event`.`section` = `sectionType`.`id` order by `call` desc", [$CHOIR, $member, $sem], QALL);
	$attendance = attendance($member, $sem);
	$attends = keyarray($attendance["attendance"], "eventNo");
	$ret = [];
	foreach (rowcast($events, [], ["gigCount", "shouldAttend", "didAttend", "confirmed"]) as $event)
	{
		$event["disabled"] = checkRsvp($event["id"], $event["shouldAttend"] == "0");
		$attend = $attends[$event["id"]];
		if ($attend)
		{
			$event["late"] = $attend["late"];
			$event["pointChange"] = $attend["pointChange"];
			$event["partialScore"] = $attend["partialScore"];
			$event["explanation"] = $attend["explanation"];
		}
		else
		{
			$event["late"] = null;
			$event["pointChange"] = null;
			$event["partialScore"] = null;
			$event["explanation"] = null;
		}
		$ret[] = $event;
	}
	reply("ok", array("finalScore" => $attendance["finalScore"], "gigCount" => $attendance["gigCount"], "gigReq" => $attendance["gigReq"], "events" => $ret));
case "attendees":
	$event = get("event");
	$ret = rowcast(query("select `attends`.`memberID` as `id`, `attends`.`shouldAttend`, `attends`.`confirmed` from `attends`, `member` where `eventNo` = ? and `attends`.`memberID` = `member`.`email` order by `member`.`lastName` asc", [$event], QALL), [], ["shouldAttend", "confirmed"]);
	reply("ok", array("attendees" => $ret));
case "members":
	//$ret = query("select concat(member.firstName, ' ', (case when member.prefName = '' then '' else concat('\"', member.prefName, '\" ') end), member.lastName) as `name`, member.email as `email`, member.phone as `phone`, member.location as `location`, activeSemester.enrollment as `enrollment` from `member`, `activeSemester` where activeSemester.member = member.email and activeSemester.semester = ?", [$SEMESTER], QALL);
	$ret = [];
	foreach (listMembers() as $email => $name) $ret[] = memberInfo($email);
	reply("ok", array("members" => $ret));
case "rsvp": // TODO Document
	$event = get("event");
	$attend = get("attend");
	if ($attend != "1" && $attend != "0") err("Invalid attend value");
	$denied = checkRsvp($event, $attend != "0");
	if ($denied) err($denied);
	query("update `attends` set `shouldAttend` = ?, confirmed = '1' where `memberID` = ? and `eventNo` = ?", [$attend, $USER, $event]);
	reply("ok", array("disabled" => checkRsvp($event, $attend == "0")));
case "songs":
	$ret = [];
	foreach (query("select * from `song` where `choir` = ?", [$CHOIR], QALL) as $song)
	{
		$song["current"] = (bool) $song["current"];
		$links = query("select `id`, `type`, `name`, `target` from `songLink` where `song` = ?", [$song["id"]], QALL);
		$linkcats = [];
		foreach ($links as $link)
		{
			$type = $link["type"];
			unset($link["type"]);
			if (! array_key_exists($type, $linkcats)) $linkcats[$type] = [];
			$linkcats[$type][] = $link;
		}
		$song["links"] = $linkcats;
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
case "requestAbsence": // TODO Document
	//$recipients = implode(", ", getPosition("Vice President")) . ", " . implode(", ", getPosition("President"));
	$recipients = "awesome@gatech.edu"; // TODO Debug remove
	if (! query("select * from `event` where `eventNo` = ?", [post("event")], QONE)) err("That event does not exist");
	if (query("select * from `absencerequest` where `memberID` = ? and `eventNo` = ?", [$USER, post("event")], QONE)) err("You have already submitted an absence request for this event");
	query("insert into `absencerequest` (reason, memberID, eventNo) values (?, ?, ?)", [post("reason"), $USER, post("event")]);
	mail($recipients, "Absence Request from " . memberName($USER, "real"), "Name:  " . memberName($USER, "real") . "\nEvent:  " . getEventName(post("event")) . "\nReason:  " . post("reason") . "\n\nView and update absence requests at https://gleeclub.gatech.edu/buzz/#absenceRequest .");
	reply("ok");
case "setList": // TODO Document
	$ret = query("select `song`.`id`, `song`.`title`, `song`.`key`, `song`.`pitch` from `song`, `gigSong` where `gigSong`.`song` = `song`.`id` and `gigSong`.`event` = ? order by `gigSong`.`order` asc", [get("event")], QALL);
	reply("ok", array("songs" => $ret));
case "confirmAccount":
	if (query("select * from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$USER, $SEMESTER, $CHOIR], QCOUNT) != 0) err("You are already confirmed");
	$loc = post("location");
	$onCampus = post("onCampus") == "true" ? 1 : 0;
	$enrollment = post("enrollment");
	$section = post("section");
	$err = updateRegistration($USER, $enrollment, $section);
	if ($err) err($err);
	query("update `member` set `location` = ?, `onCampus` = ? where `email` = ?", [$loc, $onCampus, $USER]);
	reply("ok");
case "minutes":
	$id = get("id", "");
	if ($id == "")
	{
		$minutes = query("select `id`, `name` from `minutes` order by `date` asc", [], QALL);
		reply("ok", array("minutes" => $minutes));
	}
	else
	{
		$res = query("select * from `minutes` where `id` = ? and `choir` = ?", [$id, $CHOIR], QONE);
		if (! $res) err("Those minutes do not exist");
		$ret = array("name" => $res["name"], "date" => $res["date"], "public" => $res["public"]);
		if (hasPermission("view-complete-minutes")) $ret["private"] = $res["private"];
		reply("ok", $ret);
	}
case "announcements":
	$ann = query("select `memberID` as `poster`, unix_timestamp(`timePosted`) as `posted`, `announcement` from `announcement` where `choir` = ? and `archived` != 1 order by `timePosted` asc", [$CHOIR], QALL);
	$announcements = [];
	foreach ($ann as $a)
	{
		$a["announcement"] = nl2br(htmlspecialchars($a["announcement"]));
		$announcements[] = $a;
	}
	reply("ok", array("announcements" => $announcements));
case "officers":
	$roles = [];
	foreach (query("select * from `role` where `rank` > 0 and `rank` < 90 and `choir` = ? order by `rank` asc", [$CHOIR], QALL) as $role)
	{
		$officers = keyarray(query("select `member` from `memberRole` where `role` = ?", [$role["id"]], QALL), "member");
		while (count($officers) < $role["quantity"]) $officers[] = null;
		$roles[(int) $role["id"]] = array("name" => $role["name"], "rank" => (int) $role["rank"], "officers" => $officers);
	}
	reply("ok", array("roles" => $roles));
case "semesters":
	$ret = query("select `semester`, unix_timestamp(`beginning`) as `start`, unix_timestamp(`end`) as `end` from `semester` order by `beginning` asc", [], QALL);
	reply("ok", array("semesters" => $ret));
case "absenceRequests":
	priv("process-absence-requests");
	$requests = query("select `absencerequest`.`eventNo` as `event`, unix_timestamp(`absencerequest`.`time`) as `timestamp`, `absencerequest`.`reason`, `absencerequest`.`replacement`, `absencerequest`.`memberID` as `member`, `absencerequest`.`state`, unix_timestamp(`event`.`callTime`) as `call`, `event`.`name` as `name` from  `absencerequest`, `member`, `event` where `absencerequest`.`eventNo` = `event`.`eventNo` and `absencerequest`.`memberID` = `member`.`email` and `event`.`semester` = ? order by `member`.`lastName` asc , `member`.`firstName` asc , `absencerequest`.`time` desc", [$SEMESTER], QALL);
	reply("ok", array("requests" => $requests));
case "dues":
	$vars = query("select * from `variables`", [], QONE);
	reply("ok", array("dues" => $vars["duesAmount"], "deposit" => $vars["tieDeposit"], "lateFee" => $vars["lateFee"]));
case "permissions":
	$roles = keyarray(query("select `id`, `name` from `role` where `choir` = ? and `rank` > 0", [$CHOIR], QALL), "id");
	$permissions = keyarray(query("select `name`, `description`, `type` from `permission`", [], QALL), "name");
	$grid = [];
	foreach ($roles as $role => $name) $grid[$role] = query("select `permission`, `eventType` from `rolePermission` where `role` = ?", [$role], QALL);
	reply("ok", array("roles" => $roles, "permissions" => $permissions, "rolePermissions" => $grid));
case "docLink":
	priv("edit-links");
	$type = get("type", "update");
	$name = get("name");
	if ($type == "update") query("replace into `gdocs` (`name`, `choir`, `url`) values (?, ?, ?)", [$name, $CHOIR, get("url")]);
	else if ($type == "delete") query("delete from `gdocs` where `choir` = ? and `name` = ?", [$CHOIR, $name]);
	else err("Invalid edit type \"$type\"");
	reply("ok");
case "newSemester":
	priv("edit-semesters");
	$name = get("name");
	query("insert into `semester` (`semester`, `beginning`, `end`) values (?, ?, ?)". [$name, $start, $end]);
	query("update `variables` set `semester` = ?", [$name]); // TODO Maybe wrap this in a transaction so we can't end up with inaccessible semesters hanging around
	reply("ok");
case "announce":
	priv("edit-announcements");
	$text = post("text");
	query("insert into `announcement` (`choir`, `memberID`, `timePosted`, `announcement`) values (?, ?, current_timestamp, ?)", [$CHOIR, $USER, $text]);
	$row = query("select `name`, `admin`, `list` from `choir` where `id` = ?", [$CHOIR], QONE);
	$sender = $row["name"] . " Officers <" . $row["admin"] . ">";
	$recipient = $row["name"] . " <" . $row["list"] . ">";
	$position = positions($USER)[0];
	$subject = "Important message from your $position!";
	$headers = "Reply-To: $sender\n" .
				"From: $sender\n" .
				'X-Mailer: PHP/' . phpversion();
	mail($recipient, $subject, $text, $headers);
	reply("ok");
case "archiveAnnouncement":
	priv("edit-announcements");
	query("update `announcement` set `archived` = 1 where `announcementNo` = ?", [get("id")]);
	reply("ok");
case "editMinutes":
	priv("edit-minutes");
	$id = post("id", "");
	$newname = post("newname", "");
	$private = post("private");
	$public = post("public");
	if ($id == "") $id = query("insert into `minutes` (`choir`, `date`, `name`, `private`, `public`) values (?, curdate(), ?, ?, ?)", [$CHOIR, $newname, $private, $public], QID);
	else if ($newname != "") query("update `minutes` set `name` = ?, `private` = ?, `public` = ? where `id` = ?", [$newname, $private, $public, $id]);
	else query("update `minutes` set `private` = ?, `public` = ? where `id` = ?", [$private, $public, $id]);
	reply("ok", array("id" => $id));
case "deleteMinutes":
	priv("edit-minutes");
	$id = post("id");
	query("delete from `minutes` where `id` = ?", [$id]);
	reply("ok");
case "addEvent":
	reply("ok", array("id" => doNewEvent($_POST)));
case "editEvent":
	doEditEvent($_POST);
	reply("ok");
case "deleteEvent":
	doRemoveEvent(get("id"));
	reply("ok");
case "updateCarpools":
case "updateAbsenceRequests":
case "editOfficer":
	#priv("edit-officers");
case "setPermission":
case "editUniforms":
	err("Unimplemented");
default:
	err("Unknown action \"$action\"");
}

err("Something went wrong", "Control flow reached the end of api.php without a reply() call");
?>
