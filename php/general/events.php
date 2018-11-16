<?php
require_once("vars.php");
/**** Event functions ****/

function evnullcheck($result, $kind = "event")
{
	if (! $result) die("Could not find matching $kind");
	return $result;
}

function eventTypes()
{
	$ret = array();
	foreach(query("select * from `eventType` order by `weight`", [], QALL) as $row) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function getEventDetails($eventNo){
	return evnullcheck(query("select * from `event` where `eventNo` = ?", [$eventNo], QONE));
}

function getGigDetails($eventNo){
	return evnullcheck(query("select * from `gig` where `eventNo` = ?", [$eventNo], QONE), "gig");
}

function getEventType($id){
	return evnullcheck(query("select `name` from `eventType` where `id` = ?", [$id], QONE))["name"];
}

function getEventName($eventNo){
	return evnullcheck(query("select `name` from `event` where `eventNo` = ?", [$eventNo], QONE))["name"];
}

function getEventTypeLabelClass($number){
	if($number == 'rehearsal') return 'label-info';
	if($number == 'sectional') return 'label-success';
	if($number == 'volunteer') return 'label-warning';
	if($number == 'tutti') return 'label-important';
}

function labelArea($type){
	$html = '<span class="label '.getEventTypeLabelClass($type).'">'.getEventType($type).'</span>';
	return $html;
}

function buttonArea($eventNo, $typeid)
{
	global $USER;
	$callTime = query("select `callTime` from `event` where `eventNo` = ?", [$eventNo], QONE);
	if (! $callTime) die("Invalid event ID");
	$soon = 0;
	if (strtotime($callTime["callTime"]) < time() + 86400) $soon = 1;
	
	$attend = query("select * from `attends` where `eventNo` = ? and `memberID` = ?", [$eventNo, $USER], QONE);
	if (! $attend) $html = "<span class='label'>Not attending</span>";
	else
	{
		if ($attend['shouldAttend'] == '0') $html = "<span class='label'>Not attending</span>";
		else if ($attend['confirmed'] == '0')
		{
			if ($typeid == 'volunteer')
			{
				//not confirmed volunteer gig
				if ($soon) $html = "<span class='label'>Attending</span>"; //'<div class="btn btn-confirm">Confirm I\'ll Attend</div>';
				else $html = '<div class="btn btn-primary btn-confirm" style="width:90%;">I will attend</div> <div class="btn btn-warning btn-deny" style="width:90%;">I won\'t attend</div>';
			}
			else
			{
				//not confirmed, not volunteer gig
				$html = '<div class="btn btn-confirm">Confirm I\'ll Attend</div>';
			}
		}
		else $html = '<span class="label">Attending</span>';
	}
	return $html;
}

//function requestAbsenceButton($eventNo){
	//$absenceRequest = getAbsenceRequest($eventNo, $USER);
	//if($absenceRequest['state'] == 'pending'){
		//return '<td><span class="label label-warning">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	//}
	//if($absenceRequest['state'] == 'confirmed'){
		//return '<td><span class="label label-success">absence request '.$absenceRequest['state'].'</span></td><td></td>';
	//}
	//if($absenceRequest['state'] == 'denied'){
		//return '<td><span class="label label-important">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	//}
	//else{
		//$eventDetails = getEventDetails($_SESSION['eventNo']);
		//$callTime = strtotime($eventDetails['callTime']);
		//if($callTime > time()){
			//return '<div class="btn">request absence</div>';
		//}
	//}
	////print_r($absenceRequest);
//}

function getAbsenceRequest($eventNo, $person){
	return nullcheck(query("select * from `absencerequest` where `eventNo` = ? and `memberID` = ?", [$eventNo, $person], QONE), "absence request");
}

function shouldAttend($email, $eventNo){
	$res = query("select `shouldAttend` from `attends` where `memberID` = ? and `eventNo` = ?", [$email, $eventNo], QONE);
	return $res && $res["shouldAttend"] != 0;
}

function isConfirmed($email, $eventNo){
	$res = query("select `confirmed` from `attends` where `memberID` = ? and `eventNo` = ?", [$email, $eventNo], QONE);
	return $res && $res["confirmed"] != 0;
}

// Google Calendar stuff

$calendar = "7nl6cu4fobeova68q4he7tmpuk@group.calendar.google.com";

function get_gcal()
{
	global $application, $docroot, $gcal_secret_file;
	$client = new Google_Client();
	$client->setApplicationName($application);
	$client->setAuthConfig("$docroot/secrets/$gcal_secret_file");
	$client->setScopes(["https://www.googleapis.com/auth/calendar"]);
	$service = new Google_Service_Calendar($client);
	return $service;
}

function set_event_fields($event, $title, $desc, $location, $unixstart, $unixend, $tz)
{
	global $CHOIR;
	$event->setSummary($title);
	$event->setDescription($desc);
	$event->setLocation($location);
	$start = new Google_Service_Calendar_EventDateTime();
	$start->setDateTime(date("Y-m-d\\TH:i:s", $unixstart));
	$start->setTimeZone($tz);
	$event->setStart($start);
	$end = new Google_Service_Calendar_EventDateTime();
	$end->setDateTime(date("Y-m-d\\TH:i:s", $unixend));
	$end->setTimeZone($tz);
	$event->setEnd($end);
	$creator = new Google_Service_Calendar_EventCreator();
	$res = query("select `name`, `admin` from `choir` where `id` = ?", [$CHOIR], QONE);
	if (! $res) $creator->displayName = "Georgia Tech Choirs";
	else
	{
		$creator->displayName = "Georgia Tech " . $res["name"];
		$creator->email = $res["admin"];
	}
	$event->setCreator($creator);
}
?>
