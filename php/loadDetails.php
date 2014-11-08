<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
if($_POST['id'] == 'current') $eventNo = $_SESSION['eventNo'];
else
{
	$eventNo = $_POST['id'];
	$_SESSION['eventNo'] = $eventNo;
}

$sql = "SELECT * FROM `event` WHERE eventNo=$eventNo;";
$result = mysql_query($sql);
if (mysql_num_rows($result) != 1)
{
	echo "The requested event could not be found.";
	exit(1);
}
$eventDetails = mysql_fetch_array($result);
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
			<td><span class="eventDetailsValue">'.date('M d, g:ia', strtotime($eventDetails['callTime'])).'</span></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Release Time:</td>
			<td><span class="eventDetailsValue">'.date('M d, g:ia', strtotime($eventDetails['releaseTime'])).'</span></td>
			</tr>';
if ($eventDetails['type'] == 3 || $eventDetails['type'] == 4)
{
	$sql = "select `uniform`.`name` as `uniform` from `gig`, `uniform` where `gig`.`eventNo` = '$eventNo' and `gig`.`uniform` = `uniform`.`id`";
	$res = mysql_fetch_array(mysql_query($sql));
	$html .= '<tr>
			<td class="eventDetialsKey">Uniform:</td>
		<td><span class="eventDetailsValue">'.$res['uniform'].'</span></td>
	</tr>';
}
		$html .= '<tr>
			<td style="display:none;"><span class="eventDetialsKey">Comments:</span></td>
			<td colspan="2"><p class="eventDetailsValue">'.$eventDetails['comments'].'</p></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Location:</td>
			<td><span class="eventDetailsValue">'.$eventDetails['location'].'</span></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Point Value:</td>
			<td><span class="eventDetailsValue">'.$eventDetails['points'].'</span></td>
		</tr>
';
if(isOfficer($userEmail))
{
	$gigDetails = getGigDetails($eventNo);
	$html = $html.'
		<tr>
			<td class="eventDetialsKey">Contact Name:</td>
			<td><span class="eventDetailsValue">'.$gigDetails['cname'].'</span></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Contact Email:</td>
			<td><mailto:'.$gigDetails['contactEmail'].'><span class="eventDetailsValue">'.$gigDetails['cemail'].'</span></a></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Contact Phone:</td>
			<td><span class="eventDetailsValue">'.$gigDetails['cphone'].'</span></td>
		</tr>
		<tr>
			<td class="eventDetialsKey">Price:</td>
			<td><span class="eventDetailsValue">'.$gigDetails['price'].'</span></td>
		</tr>
	';
}

$html = $html.'
	</table><table class="table no-highlight">
	<tr>
		<td colspan="2"><div class="btn" id="requestAbsenceButton" value="'.$eventNo.'">request absence</div></td>
	</tr>
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
