<?php
require_once('./functions.php');
$userEmail = getuser();

if(isset($_POST['eventNo'])){
	$eventNo = $_POST["eventNo"];

	//get the event name
	$sql = "SELECT * FROM `event` WHERE eventNo=$eventNo;";
	$event = mysql_fetch_array(mysql_query($sql));
	$eventName = $event["name"];

	//make a drop down of possible replacements
	$sql = "SELECT * FROM `member`";
	$result= mysql_query($sql);
	$dropdown = dropdown(members("active"), "replacement");

	echo "<div id='absenceRequestTable'>
			<table>
				<tr>
					<td align='center' colspan='2'><b>Request Absence for ".$eventName."</b></td>
				</tr>
				<tr>
					<td>Replacement:</td>
					<td>".$dropdown."</td>
				</tr>
				<tr>
					<td>Reason:</td>
					<td><input type='text' size='50' id='reason' /></td>
				</tr>
				<tr>
					<td><button type='button' onClick='loadDetails($eventNo);'>Never Mind</button></td>
					<td><button type='button' id='submitAbsenceRequest'>Beg for Mercy</button></td>
				</tr>
			</table>
		</div>";

}
else echo "It didn't work. :(";
?>
