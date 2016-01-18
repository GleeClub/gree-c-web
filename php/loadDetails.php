<?php
require_once('functions.php');
$userEmail = getuser();
$eventNo = $_POST['id'];

function isgig($event)
{
	return ($event['type'] == 3 || $event['type'] == 4);
}

function uniform($id)
{
	$sql = "select `uniform`.`name` as `uniform` from `gig`, `uniform` where `gig`.`eventNo` = '$id' and `gig`.`uniform` = `uniform`.`id`";
	$res = mysql_fetch_array(mysql_query($sql));
	$uni = $res['uniform'];
	$background = '#aaa';
	$html = '';
	if ($uni == 'Jeans Mode')
	{
		$background = '#137';
		$html .= 'Jeans';
	}
	else if ($uni == 'Black Slacks')
	{
		$background = '#000';
		$html .= 'Black<br>slacks';
	}
	else if ($uni == 'T-Shirt')
	{
		$background = '#dc3';
		$html .= 'T-Shirt';
	}
	else if ($uni == 'Casual')
	{
		$background = '#a8c';
		$html .= 'Casual';
	}
	else
	{
		$html .= $uni;
	}
	return "<div class='infoblock' style='background: $background'><div>$html</div></div>";
}

$sql = "SELECT * FROM `event` WHERE eventNo = '$eventNo'";
$result = mysql_query($sql);
if (mysql_num_rows($result) != 1) die("The requested event could not be found: $eventNo.");
$event = mysql_fetch_array($result);
$attends = mysql_query("select `shouldAttend`, `confirmed` from `attends` where `eventNo` = '$eventNo' and `memberID` = '$userEmail'");
if (mysql_num_rows($attends) != 1)
{
	$confirmed = 0;
	$should = 0;
}
else
{
	$row = mysql_fetch_array($attends);
	$confirmed = $row['confirmed'];
	$should = $row['shouldAttend'];
}
$html = '<style>
	h4 { font-weight: normal; }
	h5 { font-weight: normal; }
	div.infoblock { display: inline-block; border-radius: 4px; padding: 10px; margin: 4px 10px; text-transform: uppercase; color: white; font-size: 20px; line-height: 24px; font-weight: bold; height: 60px; vertical-align: middle; text-align: center; }
	div.infoblock div { position: relative; top: 50%; transform: translateY(-50%); -webkit-transform: translateY(-50%); } /* Hacky! */
	div.event-btn { display: inline-block; margin: 10px; }
	</style>';
$html .= '<div style="text-align: center"><h3><u>' . $event['name'] . '</u></h3>';
$calldate = date('l, F d, Y', strtotime($event['callTime']));
$calltime = date('g:i A', strtotime($event['callTime']));
$donedate = date('l, F d, Y', strtotime($event['releaseTime']));
$donetime = date('g:i A', strtotime($event['releaseTime']));
if (isgig($event)) $gig = getGigDetails($eventNo);
$perftime = date('g:i A', strtotime($gig['performanceTime']));
$timeinfo = '';
if ($calldate == $donedate) $timeinfo = "<b>$calldate</b> from <b>$calltime</b> to <b>$donetime</b>";
else $timeinfo = "<b>$calldate</b> at <b>$calltime</b> to <b>$donedate</b> at <b>$donetime</b>";
if (isgig($event) && $calltime != $perftime) $timeinfo .= "<h5 style='margin-top: -8px'>Performing at $perftime</h5>";
$html .= '<h4>' . $timeinfo . '</h4>';
$html .= '<h4>' . $event['location'] . '</h4></div><div style="margin: 10px 20px">';
if ($event['comments'] != '') $html .= '<div style="padding: 10px; margin-bottom: 20px">' . $event['comments'] . '</div><hr>';
$html .= '<div style="text-align: center">';
if ($should) $html .= '<div class="infoblock" style="background: #060"><div>Attending</div></div>';
else $html .= '<div class="infoblock" style="background: #c00"><div>Not<br>attending</div></div>';
if ($event['type'] == 3 && $event['gigcount'] == 0) $html .= '<div class="infoblock" style="background: #f90"><div>No gig<br>credit</div></div>';
if (isgig($event)) $html .= uniform($eventNo);
$html .= '<div class="infoblock" style="background: #888"><div><span style="font-size: 32px;">' . $event['points'] . '</span><br>points</div></div>';
$html .= '</div>';
$html .= '<div style="clear: both; text-align: center">';
if (strtotime($event['callTime']) > time())
{
	if ($event['type'] == 3 && strtotime($event['callTime']) > strtotime('+1 day'))
	{
		if (! $confirmed || ! $should) $html .= "<div id='attend_will' class='btn event-btn' onclick='should_attend($eventNo, \"$userEmail\", 1)'>I <span style='color: green'>WILL</span><br>Attend</div>";
		if (! $confirmed || $should) $html .= "<div id='attend_wont' class='btn event-btn' onclick='should_attend($eventNo, \"$userEmail\", 0)'>I <span style='color: red'>WILL NOT</span><br>Attend</div>";
	}
	else if ($should)
	{
		if (! $confirmed) $html .= "<div id='attend_confirm' class='btn event-btn btn-primary' onclick='is_confirmed($eventNo, \"$userEmail\", 1)'>Confirm I<br>Will Attend</div>";
		$html .= "<div id='requestAbsenceButton' class='btn event-btn'>Request<br>Absence</div>";
	}
	else $html .= "<div id='attend_will' class='btn event-btn' onclick='should_attend($eventNo, \"$userEmail\", 1)'>I <span style='color: green'>WILL</span><br>Attend</div>";
}
if (isgig($event))
{
	$html .= '<div id="attendingButton" class="btn event-btn">See Who\'s<br>Attending</div>';
	$html .= '<div id="carpoolsButton" class="btn event-btn">View<br>Carpools</div>';
	$html .= '<div id="setlistButton" class="btn event-btn">Set<br>List</div>';
}
if (isOfficer($userEmail)) $html .= '<div id="attendanceButton" class="btn event-btn" onclick="updateEventAttendance(\'' . $eventNo . '\')">Update<br>Attendance</div>';
if (canEditEvents($userEmail)) $html .= '<div id="editButton" class="btn event-btn">Edit<br>Event</div>';
if (positionFromEmail($userEmail) == 'Section Leader' && getEventType($event['type']) == "Sectional")
{
	if ($event['section'] == sectionFromEmail($userEmail)) $html .= '<div id="attendanceButton" class="btn event-btn" onclick="updateEventAttendance(\'' . $eventNo . '\')">Update<br>Attendance</div>';
}
$html .= "</div>";
if(isOfficer($userEmail))
{
	$html .= '<hr>';
	if (isgig($event))
	{
		if ($gig['cname'] != '' || $gig['cphone'] != '' || $gig['$cemail']) $html .= '<b>Contact</b><br>Name: ' . $gig['cname'] . '<br>Email: <a href="mailto:' . $gig['cemail'] . '">' . $gig['cemail'] . '</a><br>Phone: ' . $gig['cphone'] . '<br>Price: $' . $gig['price'] . '<br>';
	}
}
$html .= '</div>';

echo $html;
?>

