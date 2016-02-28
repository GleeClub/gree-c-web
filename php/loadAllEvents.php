<?php
require_once('functions.php');
$userEmail = getuser();
if (! getuser()) die("<p>It would seem that you are logged out.</p>");
$choir = getchoir();
if (! $choir) die("Choir not set");

$type = mysql_real_escape_string($_POST['type']);
$cond = "`choir` = '$choir'";
if ($type != "all" && $type != "past") $cond = "`type` = '$type'";
if (! isOfficer($userEmail))
{
	if ($cond != '') $cond .= " and ";
	$cond .= "exists(select * from `attends` where `eventNo` = `event`.`eventNo` and `memberID` = '$userEmail')";
}
if ($type != 'past')
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
