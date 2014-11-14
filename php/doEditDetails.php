<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

if (! isOfficer($userEmail)) die("Access denied");

foreach ($_POST as $k => $v) $_POST[$k] = mysql_real_escape_string($v);
$id = $_POST['id'];
$name = $_POST['name'];
$type = $_POST['type'];
if ($type < 0 || $type > 4) die("Bad event type");
if (! valid_date($_POST['calldate'])) die("Bad call date");
if (! valid_date($_POST['donedate'])) die("Bad done date");
if (! valid_time($_POST['calltime'])) die("Bad call time");
if (! valid_time($_POST['donetime'])) die("Bad done time");
$unixcall = strtotime($_POST['calldate'] . ' ' . $_POST['calltime']);
$unixperf = strtotime($_POST['calldate'] . ' ' . $_POST['perftime']);
$unixdone = strtotime($_POST['donedate'] . ' ' . $_POST['donetime']);
if ($unixcall > $unixdone) die("Event must start before it ends");
if (($type == 3 || $type == 4) && ($unixperf < $unixcall || $unixperf > $unixdone)) die("Performance time must be between start and end");
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

if (! mysql_query("update `event` set `name` = '$name', `callTime` = '$call', `releaseTime` = '$done', `points` = '$points', `comments` = '$comments', `type` = '$type', `location` = '$location', `semester` = '$semester', `gigcount` = '$gigcount' where `eventNo` = '$id'")) die(mysql_error());
if (($type == 3 || $type == 4) && ! mysql_query("update `gig` set `performanceTime` = '$perf', `uniform` = '$uniform', `cname` = '$cname', `cphone` = '$cphone', `cemail` = '$cemail', `price` = '$price', `public` = '$public', `summary` = '$summary', `description` = '$description' where `eventNo` = '$id'")) die(mysql_error());
echo "$id";
?>

