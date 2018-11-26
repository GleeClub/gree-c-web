<?php
require_once('functions.php');
if (! $USER) err("<p>It would seem that you are logged out.</p>");
if (! $CHOIR) err("Choir not set");

$type = $_POST['type'];
$cond = ["`choir` = ?"];
$vars = [$CHOIR];
if ($type != "all" && $type != "past")
{
	$cond[] = "`type` = ?";
	$vars[] = $type;
}
if (! hasPermission("view-all-events"))
{
	$cond[] = "exists(select * from `attends` where `eventNo` = `event`.`eventNo` and `memberID` = ?)";
	$vars[] = $USER;
}
if ($type != 'past')
{
	$cond[] = "`semester` = ?";
	$vars[] = $SEMESTER;
}

$events = query("select `eventNo` from `event` where " . implode($cond, " and ") . " order by `callTime` desc", $vars, QALL);
echo '<div class="block span5" id="events"><table class="table" id="eventsTable">';
if (count($events) == 0) echo "(No events yet this semester)";
foreach ($events as $row)
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
