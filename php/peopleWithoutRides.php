<?php
require_once('functions.php');
$eventNo = $_POST['eventNo'];

$needRides = array();
$results = query("
	SELECT email,shouldAttend,attends.confirmed 
	FROM member,attends
	WHERE
		email NOT IN (
			SELECT email FROM carpool, ridesin LEFT JOIN member 
				ON email=memberID 
			WHERE carpool.carpoolID=ridesin.carpoolID AND carpool.eventNo=?)
		AND exists (select * from `activeSemester` where `activeSemester`.`semester` = ? and `activeSemester`.`member` = `member`.`email`)
		AND attends.eventNo=?
		AND attends.memberID=email
	ORDER BY shouldAttend DESC, lastName ASC, firstName ASC", [$eventNo, $SEMESTER, $eventNo], QALL);

foreach ($results as $personInNeedOfRide)
{
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
			<td>".memberName($email)."</td>
			<td class='passengerSpots'>$passengerSpots</td>
			<td>$shouldAttendHTML</td>
			<td>$confirmedHTML</td>
		</tr>
	</table></div>";
}

?>
