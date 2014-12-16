<?php
error_reporting(E_ALL);
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
$eventNo = $_SESSION['eventNo'];
$isOfficer = isOfficer($userEmail);
if(isOfficer($userEmail)){
	$html .= '<div class="btn" id="editCarpoolsButton">edit carpools</div>';
}
$html .= "<div id='carpools'>";

$sql = "SELECT * FROM `carpool` WHERE eventNo=$eventNo;";
$result = mysql_query($sql);
//echo $sql;
//echo $result;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	$html .= "<div class='carpool block' id='".$row['carpoolID']."'>";
	$driver = $row['driver'];
	//$shouldAttend='';
	//$isConfirmed ='';
	//$passengerSpots=0;
	$emails = prefFullNameFromEmail($driver) . ' <' . $driver . '>';
	$shouldAttend = (shouldAttend($driver, $eventNo) == true) ? '<span class="label label-info">should</span>' : '<span class="label label-important">shouldn\'t</span>';
	$isConfirmed = (isConfirmed($driver, $eventNo) == true) ? '<span class="label label-info">confirmed</span>' : '<span class="label label-warning">unconfirmed</span>';
	$passengerSpots = (passengerSpots($driver) !== "0") ? "<span class='badge badge-info'>".passengerSpots($driver)."</span>" : "";
	$livesAt = "<span class='label'>".livesAt($driver)."</span>";
	$phoneNumber = "<a href='tel:".phoneNumber($driver)."'>".phoneNumber($driver)."</a>";
	if(isOfficer($userEmail)){
		$html .= "<div class='driver block'><div class='person' id='".$driver."'><table>
		<tr>
			<td class='carpoolLives'>".$livesAt."</td>
			<td class='carpoolName'><a href='mailto:$driver'>".prefFullNameFromEmail($driver)."</a></td>
			<td class='carpoolSpots'>".$passengerSpots."</td>
			<td class='carpoolShould'>".$phoneNumber."</td>
			<td class='carpoolConfirmed'>".$isConfirmed."</td>
		</tr>
		</table></div></div>";
	}
	else{
		$html.="
			<tr>
				<td class='carpoolLives'>".$livesAt."</td>
				<td class='carpoolName'>".prefFullNameFromEmail($driver)."</td>
				<td class='carpoolSpots'>".$passengerSpots."</td>
				<td class='carpoolShould'>".$phoneNumber."</td>
				<td class='carpoolConfirmed'>".$isConfirmed."</td>
			</tr>
			</table></div></div>
			";
	}
	//$passengers = array();
	$carpoolDetails = getCarpoolDetails($row['carpoolID']);
	$html .= "<div class='passengers block'>";
	while($passenger = mysql_fetch_array($carpoolDetails)){
		//$passengers[] = $passenger['memberID'];
		if($passenger['memberID'] !== $driver){
			//$shouldAttend='';
			//$isConfirmed ='';
			//$passengerSpots=0;
			$emails .= ', ' . prefFullNameFromEmail($passenger['memberID']) . ' <' . $passenger['memberID'] . '>';
			$shouldAttend = (shouldAttend($passenger['memberID'], $eventNo) == true) ? '<span class="label label-info">should</span>' : '<span class="label label-important">shouldn\'t</span>';
			$isConfirmed = (isConfirmed($passenger['memberID'], $eventNo) == true) ? '<span class="label label-info">confirmed</span>' : '<span class="label label-warning">unconfirmed</span>';
			$passengerSpots = (passengerSpots($passenger['memberID']) !== "0") ? "<span class='badge badge-info'>".passengerSpots($passenger['memberID'])."</span>" : '';
			$livesAt = "<span class='label'>".livesAt($passenger['memberID'])."</span>";
			$phoneNumber = "<a href='tel:".phoneNumber($passenger['memberID'])."'>".phoneNumber($passenger['memberID'])."</a>";
			$html .= "<div class='person' id='".$passenger['memberID']."'><table>
			<tr>
				<td class='carpoolLives'>".$livesAt."</td>
				<td class='carpoolName'><a href='mailto:".$passenger['memberID']."'>".prefFullNameFromEmail($passenger['memberID'])."</a></td>
				<td class='carpoolSpots'>".$passengerSpots."</td>
				<td class='carpoolShould'>".$phoneNumber."</td>
				<td class='carpoolConfirmed'>".$isConfirmed."</td>
				</tr>
			</table></div>";
		}
	}
	$sql = "select `name` from `event` where `eventNo` = $eventNo";
	$event = mysql_fetch_array(mysql_query($sql));
	$html .= '</div>';//end passengers div
	$html .= '<div style="display: inline-block; width: 100%"><a href="mailto:' . rawurlencode($emails) . '?cc=gleeclub_officers@lists.gatech.edu' . '&subject=' . rawurlencode('Carpool for ' . $event['name']) . '" class="btn pull-right"><i class="icon-envelope"></i>&nbsp;Mail these SLOOTS</a></div>';
	$html .= "</div>";//end carpool div
}
$html .= "</div>";

echo $html;

?>