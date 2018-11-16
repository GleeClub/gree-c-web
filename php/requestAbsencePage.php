<?php
require_once('./functions.php');

if(isset($_POST['eventNo'])){
	$eventNo = $_POST["eventNo"];

	//get the event name
	$event = query("select `name` from `event` where `eventNo` = ?", [$eventNo], QONE);
	if (! $event) die("No such event");
	$eventName = $event["name"];

	//make a drop down of possible replacements
	$dropdown = dropdown(listMembers(), "replacement");

	echo "<div id='absenceRequestTable'>
			<table>
				<tr>
					<td align='center' colspan='2'><b>Request Absence for $eventName</b></td>
				</tr>
				<tr>
					<td>Replacement:</td>
					<td>$dropdown</td>
				</tr>
				<tr>
					<td>Reason:</td>
					<td><input type='text' size='50' id='reason' /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><button type='button' id='submitAbsenceRequest'>Beg for Mercy</button></td>
				</tr>
			</table>
		</div>";

}
else echo "It didn't work. :(";
?>
