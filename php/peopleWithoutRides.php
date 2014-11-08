	<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
$eventNo = $_SESSION['eventNo'];

$needRides = array();
$sql = "
	SELECT email,shouldAttend,attends.confirmed 
	FROM member,attends 
	WHERE
		email NOT IN (
			SELECT email FROM carpool, ridesin LEFT JOIN member 
				ON email=memberID 
			WHERE carpool.carpoolID=ridesin.carpoolID AND carpool.eventNo=$eventNo)
		AND member.confirmed=1
		AND attends.eventNo=$eventNo
		AND attends.memberID=email
	ORDER BY shouldAttend DESC, attends.confirmed DESC,lastName ASC, firstName ASC";

$results = mysql_query($sql);
while($personInNeedOfRide = mysql_fetch_array($results)){
	$email = $personInNeedOfRide['email'];
	$shouldAttend = $personInNeedOfRide['shouldAttend'];
	$confirmed = $personInNeedOfRide['confirmed'];

	$shouldAttendHTML = ($shouldAttend == '1') ? '<span class="label label-info">should</span>' : '<span class="label label-important">shouldn\'t</span>';
	$confirmedHTML = ($confirmed == '1') ? '<span class="label label-info">confirmed</span>' : '<span class="label label-warning">unconfirmed</span>';
	$passengerSpots = (passengerSpots($email) !== "0") ? "<span class='badge badge-info'>".passengerSpots($email)."</span>" : "";
	$livesAt = "<span class='label'>".livesAt($email)."</span>";
	echo "<div class='person' id='".$email."'><table>
		<tr>
			<td>$livesAt</td>
			<td>".prefFullNameFromEmail($email)."</td>
			<td class='passengerSpots'>$passengerSpots</td>
			<td>$shouldAttendHTML</td>
			<td>$confirmedHTML</td>
		</tr>
	</table></div>";
}

?>