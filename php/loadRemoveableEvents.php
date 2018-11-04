<?php
require_once('functions.php');

$types = explode('^',$_POST['types']);
$semesters = explode('^',$_POST['semesters']);

$tsub = "(" . implode(", ", array_fill(0, count($types), "?")) . ")";
$ssub = "(" . implode(", ", array_fill(0, count($semesters), "?")) . ")";
$query = query("select * from `event` where `type` in $tsub and `semester` in $ssub order by `callTime` desc", array_merge($types, $semesters), QALL);

$html = '
<div id="events">
	<table class="table" id="eventsTable">';
foreach ($query as $row)
{
	$eventDetails = getEventDetails($row['eventNo']);
	$html = $html.  "
		<tr id='".$eventDetails['eventNo']."_row'>
			<td>".labelArea($eventDetails['type'])."</td>
			<td>".$eventDetails['name']."</td>
			<td>".date('D, M d, Y', strtotime($eventDetails['callTime']))."</td>
			<td><button type=\"button\" class=\"btn btn-danger btn-mini removeButton\" value=\"".$eventDetails['name']."\" id=\"".$row['eventNo']."\">Remove</button></td>
		</tr>";
}
echo $html.'
	</table>
</div>';

?>
