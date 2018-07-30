<?php
require_once('functions.php');
require_once("$docroot_external/php/lib/google-api-php-client-2.1.3/vendor/autoload.php");

function gcalUpdate($id, $title, $location, $desc, $unixstart, $unixend)
{
	global $calendar;
	$tz = date_default_timezone_get();
	$cal = get_gcal();

	$event = $cal->events->get($calendar, "calev" . $id);
	set_event_fields($event, $title, $desc, $location, $unixstart, $unixend, $tz);
	$cal->events->patch($calendar, "calev" . $id, $event);
}

foreach ($_POST as $k => $v) $_POST[$k] = mysql_real_escape_string($v);
$id = $_POST['id'];
$name = $_POST['name'];
$type = $_POST['type'];
if (! hasEventPermission("modify", $id)) die("Access denied");

#if ($type < 0 || $type > 4) die("Bad event type"); # TODO
if (! valid_date($_POST['calldate'])) die("Bad call date");
if (! valid_date($_POST['donedate'])) die("Bad done date");
if (! valid_time($_POST['calltime'])) die("Bad call time");
if (! valid_time($_POST['donetime'])) die("Bad done time");
$perftime = $_POST['perftime'];
if ($perftime == '') $perftime = $_POST['calltime'];
if (! valid_time($perftime)) die("Badd performance time");
$unixcall = strtotime($_POST['calldate'] . ' ' . $_POST['calltime']);
$unixperf = strtotime($_POST['calldate'] . ' ' . $perftime);
$unixdone = strtotime($_POST['donedate'] . ' ' . $_POST['donetime']);
if ($unixcall > $unixdone) die("Event must start before it ends");
if (($type == 'volunteer' || $type == 'tutti') && ($unixperf < $unixcall || $unixperf > $unixdone)) die("Performance time must be between start and end");
$call = date("Y-m-d H:i:s", $unixcall);
$perf = date("Y-m-d H:i:s", $unixperf);
$done = date("Y-m-d H:i:s", $unixdone);
$points = $_POST['points'];
$comments = $_POST['comments'];
$location = $_POST['location'];
$semester = $_POST['semester'];
$gigcount = isset($_POST['gigcount']) ? 1 : 0;
$uniform = $_POST['uniform'];
$cname = $_POST['cname'];
$cphone = $_POST['cphone'];
$cemail = $_POST['cemail'];
$price = $_POST['price'];
$public = isset($_POST['public']) ? 1 : 0;
$summary = $_POST['summary'];
$description = $_POST['description'];
$defaultAttend = isset($_POST['defaultAttend']) ? "true" : "false";

if (! mysql_query("update `event` set `name` = '$name', `callTime` = '$call', `releaseTime` = '$done', `points` = '$points', `comments` = '$comments', `type` = '$type', `location` = '$location', `semester` = '$semester', `gigcount` = '$gigcount', `defaultAttend` = $defaultAttend where `eventNo` = '$id'")) die(mysql_error());
if (($type == 'volunteer' || $type == 'tutti') && ! mysql_query("update `gig` set `performanceTime` = '$perf', `uniform` = '$uniform', `cname` = '$cname', `cphone` = '$cphone', `cemail` = '$cemail', `price` = '$price', `public` = '$public', `summary` = '$summary', `description` = '$description' where `eventNo` = '$id'")) die(mysql_error());
gcalUpdate($id, $name, $location, $comments, $unixcall, $unixdone);
echo "$id";
?>
