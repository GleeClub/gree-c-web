<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
$_SESSION['otherPerson'] = $_POST['person'];
$otherPerson = $_SESSION['otherPerson'];

$html = '
	<table class="table" id="messageThreadTable">
	<tr>
		<td><div class="btn" id="backToMessagesList"><i class="icon-arrow-left"></i> back</div></td>
		<td><h2>'.firstNameFromEmail($otherPerson).' '.lastNameFromEmail($otherPerson).'</h2></td>
	</tr>';
$sql = "SELECT * FROM `message` WHERE (sender='".$userEmail."' AND recipient='".$otherPerson."') OR (sender='".$otherPerson."' AND recipient='".$userEmail."') ORDER BY `timeSent` ASC;";
//echo $sql;
$results = mysql_query($sql);
$lighter = " lighter ";
while($row = mysql_fetch_array($results)){
	$timeInt = strtotime($row["timeSent"]);
	$time = date("H:i", $timeInt);
	$day = date("M j", $timeInt);
	$html = $html. '<tr class="message'.$lighter.'">
		<td class="messageSender"><span class="messageTime">'.$day.' '.$time.'</span>'.prefNameFromEmail($row['sender']).' '.lastNameFromEmail($row['sender']).':</td>
		<td>'.$row['contents'].'</td>
	</tr>';
	if($lighter==''){$lighter = " lighter";}
	else{$lighter='';}
}
$html = $html. '
	<tr class="form-inline'.$lighter.'">
		<td class="messageSender">You:</td>
		<td><div class="control-group"><input type="text" id="messageText"/> <div class="btn btn-primary" id="sendMessageButton">send</div></div></td>
	</tr>
	</table>';
echo $html;
?>