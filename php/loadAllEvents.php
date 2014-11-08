<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

if(!isset($_COOKIE['email']))
{
	echo "<p>It would seem that you are logged out.</p>";
	exit(1);
}

//Grab all events for a user that they should attend for the current semester
function shouldAttendEvents($userEmail, $type = 'allEvents')
{
	$CUR_SEM = getCurrentSemester();
	$t = '';
	switch($type)
	{
		case "other":
			$t = ' AND type = 0';
			break;
		case "rehearsal":
			$t = ' AND type = 1 ';
			break;
		case "sectional":
			$t = ' AND type = 2 ';
			break;
		case "volunteer":
			$t = ' AND type = 3 ';
			break;
		case "tutti":
			$t = ' AND type = 4 ';
			break;
	}

	if(isOfficer($userEmail))
	{
		if($t !== ''){$t = substr($t, 4);}
		else{$t='type >= 0';}
		$sql = "select * from (
		select callTime as time, name as occurrence, event.eventNo, semester 
		from event where $t ORDER BY time DESC)
		as res where semester='$CUR_SEM'";
	}
	else
	{
		$sql = "select * from (
		select callTime as time, name as occurrence, event.eventNo, semester 
		from event, attends
		where memberID='$userEmail' AND event.eventNo=attends.eventNo $t ORDER BY time DESC)
		as res where semester='$CUR_SEM'";
	}
	$results = mysql_query($sql);
	return $results;
}

function eventExtras()
{
	$html = '
		<div class="block span6" id="eventDetails">
			<p>Select an event</p>
		</dev>
	';
	echo $html;
}

$html = '<div class="block span5" id="events"><table class="table" id="eventsTable">';
$events = shouldAttendEvents($userEmail, $_POST['type']);
while($row = mysql_fetch_array($events, MYSQL_ASSOC))
{
	$eventDetails = getEventDetails($row['eventNo']);
	$html = $html.  '
	<tr class="event" id="'.$eventDetails['eventNo'].'">
		<td>'.labelArea($eventDetails['type']).'</td>
		<td>'.$eventDetails['name'].'</td>
		<td>'.date("l, F d", strtotime($eventDetails["callTime"])).'</td>
		<td>'.((strtotime($eventDetails['callTime']) > time() ) ? buttonArea($row['eventNo'], $eventDetails['type']) : '<span class="label label-inverse">This event is over</span>').'</td>
	</tr>
	';
}
echo $html.'</table></div>';

eventExtras();
?>
