<?php
require_once('./functions.php');
$userEmail = $_COOKIE['email'];
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

if(isset($_POST['eventNo'])){
	$eventNo = $_POST["eventNo"];

	//get the event name
	$sql = "SELECT * FROM `event` WHERE eventNo=$eventNo;";
	$event = mysql_fetch_array(mysql_query($sql));
	$eventName = $event["name"];

	//make a drop down of possible replacements
	$sql = "SELECT * FROM `member`";
	$result= mysql_query($sql);
	$dropdown="<select id='replacement'><option value='' id='null'>nobody :(</option>";
	while($row = mysql_fetch_array($result)){
		if($row['email']!=$userEmail)
			$dropdown=$dropdown."<option ".$default." value='".$row["email"]."'>".$row["firstName"]." ".$row["lastName"]."</option>";
	}
	$dropdown=$dropdown."</select>";


	echo "<div id='absenceRequestTable'>
			<table>
				<tr>
					<td align='center' colspan='2'>Request Absence for ".$eventName."</td>
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
					<td><button type='button' onClick='loadDetails($eventNo);'>nevermind</button></td>
					<td><button type='button' id='submitAbsenceRequest'>beg for mercy</button></td>
				</tr>
			</table>
		</div>";

}
else echo "It didn't work. :(";
?>
