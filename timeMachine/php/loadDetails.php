<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];
//echo 'post: '.$_POST['id'];
if($_POST['id'] == 'current'){
	$eventNo = $_SESSION['eventNo'];
}
else{
	$eventNo = $_POST['id'];
	$_SESSION['eventNo'] = $eventNo;
}
$eventDetails = getEventDetails($eventNo);
$html = '
	<table class="table no-highlight no-border">
		<tr>
			<td class="eventDetialsKey" style="display:none;">Name:</td>
			<td style="text-align:right;"><h3 class="eventDetailsValue">'.$eventDetails['name'].'</h3></td>
			<td class="eventDetialsKey" style="display:none;">Date:</td>
			<td><h3><span class="eventDetailsValue">'.date('l F d, Y', strtotime($eventDetails['callTime'])).'</span></h3></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Call Time:</td>
			<td><span class="eventDetailsValue">'.date('g:ia', strtotime($eventDetails['callTime'])).'</span></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Release Time:</td>
			<td><span class="eventDetailsValue">'.date('g:ia', strtotime($eventDetails['releaseTime'])).'</span></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Uniform:</td>
			<td><span class="eventDetailsValue">'.$eventDetails['uniform'].'</span></td>
		</tr>
		<tr>
			<td style="display:none;"><span class="eventDetialsKey">Comments:</span></td>
			<td colspan="2"><p class="eventDetailsValue">'.$eventDetails['comments'].'</p></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Location:</td>
			<td><span class="eventDetailsValue">'.$eventDetails['location'].'</span></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Point Value:</td>
			<td><span class="eventDetailsValue">'.$eventDetails['pointValue'].'</span></td>
		</tr>
		</table>
';
if(isOfficer($userEmail)){
	$gigDetails = getGigDetails($eventNo);
	$html = $html.'
		<div class="block">
		<table class="table no-highlight">
		<tr>
			<td class="eventDetialsKey">Contact Name:</td>
			<td><span class="eventDetailsValue">'.$gigDetails['contactName'].'</span></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Contact Email:</td>
			<td><mailto:'.$gigDetails['contactEmail'].'><span class="eventDetailsValue">'.$gigDetails['contactEmail'].'</span></a></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Contact Phone:</td>
			<td><span class="eventDetailsValue">'.$gigDetails['contactPhone'].'</span></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Price:</td>
			<td><span class="eventDetailsValue">'.$gigDetails['price'].'</span></td>
		</tr>
		</table>
		</div>
	';
}

$html = $html.'
	<table class="table no-highlight">
	<tr>
		<td colspan="2"><div class="btn" id="attendingButton">see who\'s attending</div></td>
	</tr>
	<tr>
		<td colspan="2"><div class="btn" id="carpoolsButton">carpools</div></td>
	</tr>
';

if(isOfficer($userEmail) || (positionFromEmail($userEmail) == 'Section Leader' && getEventType($eventDetails['type']) == "Sectional")){
	$html = $html.'
		<tr>
			<td><div class="btn" id="attendanceButton" onclick="updateEventAttendance(\''.$eventNo.'\');">update attendance</div></td>
		</tr>
		<tr>
			<td id="editButtonTd"><div class="btn" id="editButton">edit details</div></td>
		</tr>
	';
}

$html = $html.'</table>';
echo $html;

?>
