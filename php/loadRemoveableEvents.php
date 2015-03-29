<?php
require_once('functions.php');
$userEmail = getuser();

$types = array();
$semesters = array();

$types = explode('^',$_POST['types']);
$semesters = explode('^',$_POST['semesters']);

$tCount = 0;
$t = "";
foreach($types as $type){
	if($tCount>0)
		$t.=" or ";
	$t.= "type='$type'";
	$tCount++;
}

$sCount = 0;
$s = "";
foreach($semesters as $semester){
	if($sCount>0)
		$s.=" or ";
	$s.= "semester='$semester'";
	$sCount++;
}

$sql = "select * from event where ($t) AND ($s) order by callTime desc";
$events = mysql_query($sql);

$html = '
<div id="events">
	<table class="table" id="eventsTable">';
while($row = mysql_fetch_array($events, MYSQL_ASSOC)){
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