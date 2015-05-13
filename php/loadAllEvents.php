<?php
require_once('functions.php');
$userEmail = getuser();

if (! getuser()) die("<p>It would seem that you are logged out.</p>");

$type = $_POST['type'];
$cond = '';
switch($type)
{
	case 'other':     $cond = "`type` = '0'"; break;
	case 'rehearsal': $cond = "`type` = '1'"; break;
	case 'sectional': $cond = "`type` = '2'"; break;
	case 'volunteer': $cond = "`type` = '3'"; break;
	case 'tutti':     $cond = "`type` = '4'"; break;
	case 'allEvents': case 'pastEvents': break;
	default: die("Unknown event type \"$type\"");
}
if (! isOfficer($userEmail))
{
	if ($cond != '') $cond .= " and ";
	$cond .= "exists(select * from `attends` where `eventNo` = `event`.`eventNo` and `memberID` = '$userEmail')";
}
if ($type != 'pastEvents')
{
	if ($cond != '') $cond .= " and ";
	$cond .= "`semester` = '$CUR_SEM'";
}
if ($cond != '') $cond = "where $cond";

$sql = "select `eventNo` from `event` $cond order by `callTime` desc";
$events = mysql_query($sql);
if (! $events) die(mysql_error());

echo '<div class="block span5" id="events"><table class="table" id="eventsTable">';
if (mysql_num_rows($events) == 0) echo "(No events yet this semester)";
while($row = mysql_fetch_array($events, MYSQL_ASSOC))
{
	$eventDetails = getEventDetails($row['eventNo']);
	echo '<tr class="event" id="'.$eventDetails['eventNo'].'">
		<td style="">'.labelArea($eventDetails['type']).'</td>
		<td>'.$eventDetails['name'].'</td>
		<td style="min-width: 8em">'.date("D, M d", strtotime($eventDetails["callTime"])).'</td>
		<td style="">'.((strtotime($eventDetails['callTime']) > time() ) ? buttonArea($row['eventNo'], $eventDetails['type']) : '<span class="label label-inverse">This event is over</span>').'</td>
	</tr>';
}
echo '</table></div>';

echo '<div class="block span6" id="eventDetails"><p>Select an event</p></dev>';

?>
