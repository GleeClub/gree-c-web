<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];

if(!isset($_COOKIE['email'])){
	echo "<p>It would seem that your are logged out...?</p>";
}

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

$html = '<div class="block span5" id="events"><table class="table" id="eventsTable">';
while($row = mysql_fetch_array($events, MYSQL_ASSOC)){
	$eventDetails = getEventDetails($row['eventNo']);
	$html = $html.  '
	<tr class="event" id="'.$eventDetails['eventNo'].'">
		<td>'.labelArea($eventDetails['type']).'</td>
		<td>'.$eventDetails['name'].'</td>
		<td>'.date("l, F d, Y", strtotime($eventDetails["callTime"])).'</td>
		<td>'.((strtotime($eventDetails['callTime']) > time() ) ? buttonArea($row['eventNo'], $eventDetails['type']) : '<span class="label label-inverse">This event is over</span>').'</td>
	</tr>
	';
}
echo $html.'</table></div>';

eventExtras();
?>
