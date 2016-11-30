<?php
require_once('variables.php');
require_once('taylorFunctions.php');

function getCurrentSemester() {
	$sql = "SELECT semester FROM variables";
	$arr = mysql_fetch_array(mysql_query($sql));
	return $arr['semester'];
}


function newMessageForm() {
	?>
	<form method="post" id="newMessageForm" action="php/createMessage.php">
	To: <br /><input type="text" id="members" name="members" /> <br />
	Title: <br /><input id="title" type="text" name="title" /> <br />
	Message: <br /><textarea id="message" rows="5" type="text" name="message" wrap="physical" maxlength="1024"></textarea><br />
	<div class="btn btn-primary" onclick='return checkForm();'>Submit</div>
	<div class="btn" onclick='window.location.hash="messages";'>Back</div>
	</form>

	<script type="text/javascript">
		$("#newMessageForm").submit(function(event) {
			event.preventDefault();
			alert('working');
			$.post("php/createMessage.php", $("#newMessageForm").serialize(), function(data) {loadMessage(parseInt(data, 10));});
			return false;
		});
	</script>
	<?php
}


//Get a list of messages for the user
function getMessages($email) {
	$sql = "select * from convoMaster left join convoMembers on convoMaster.id=convoMembers.id where convoMembers.email='$email' order by convoMaster.modified desc";
	$res = mysql_query($sql);
	echo "<h2>Inbox</h2><br />";
	echo "<table>";
	while($arr = mysql_fetch_array($res)) {
		$title = $arr['title'];
		$new = $arr['newMessages'];
		$id = $arr['id'];
		//Make the string bold and show the number of new messages if any exists.
		$newstr = $new == 0 ? $title : "<b>$title <span class='badge badge-info'>$new</span></b>";
		echo "<tr><td><a href='#message?id=$id' onclick='return loadMessage($id);'>$newstr</a></td></tr>";
	}
	echo "</table><br />";

	echo "<div class=\"btn btn-primary\" onclick='window.location.hash=\"newMessage\"'>New Message</div>";
}
//most recent message time for inbox
function mostRecentMessageTime($id){
	$sql = "SELECT timestamp FROM `convoMessages` where id=$id order by timestamp desc";
	$first = mysql_fetch_array(mysql_query($sql));
	return $first['timestamp'];
	//return "time";
}

//Get message $id, and set it as read for $email.
function getMessage($id, $email) {
	$sql = "update convoMembers set newMessages = 0 where id='$id' and email='$email'";
	mysql_query($sql);


	//Get the title for the convo
	$sql = "select title from convoMaster where id=$id";
	$arr = mysql_fetch_array(mysql_query($sql));
	$title = $arr['title'];

	//Get the convo members
	$sql = "select distinct member.prefName from convoMembers left join member on member.email=convoMembers.email where convoMembers.id='$id' and convoMembers.email<>'" . mysql_real_escape_string($_COOKIE['email']) ."'";
	$res = mysql_query($sql);
	$members = "You";
	while($arr = mysql_fetch_array($res)) {
		$members .= ", " . $arr['prefName'];
	}

	$sql = "select message, member.prefName from convoMessages left join member on member.email=convoMessages.sender where id='$id' order by timestamp asc";
	$res = mysql_query($sql);
	echo "<h1>$title</h1>";
	echo "<h3>Between $members</h3>";
	echo "<table>";
	while($arr = mysql_fetch_array($res)) {
		echo "<tr>";
		$pn = $arr['prefName'];
		$msg = $arr['message'];
		echo "<td>$pn:</td>";
		echo "<td>$msg</td>";
		echo "</tr>";
	}
	echo "</table>";


	?>
	<script type="text/javascript">
		$("#messageSubmit").submit(function(event) {
			event.preventDefault();
			$.post("php/doReply.php", $("#messageSubmit").serialize(), function(data) {loadMessage(<?php echo $id ?>);});
			return false;
		});
	</script>
	<form id="messageSubmit" method="post" action=""> 
		<input type="hidden" name="msgID" value="<?php echo $id; ?>" />
		<textarea rows="5" type="text" name="message" wrap="physical" maxlength="1024"></textarea><br />
		<div class="btn btn-primary" id="messageFormSubmit" onclick="$('#messageSubmit').submit()">Reply</div>
		<div class="btn" onclick="window.location.hash='#messages'">Back</div>
	</form>
	<?php
}


function retrieveAttendanceHistory($userEmail){
	$CUR_SEM = getCurrentSemester();
	if(!isset($_COOKIE['email'])){
		loginBlock();
		return;
	}
	$sql = 'SELECT * FROM `member` WHERE email="'.$userEmail.'";';
	$result = mysql_fetch_array(mysql_query($sql));
	//print_r($results);
	
	$type = $result["position"];
	$first = $result["prefName"];
	
	if(empty($first)) {  //In case they didn't submit a preferred name
		$first = $result["firstName"];
	}
	
	echo '<div class="block span5" id="attendanceHistory"><h2>Attendance History</h2>';
	$grade = 100;
	
	$sql = "SELECT * FROM $tbl_name WHERE email='$userEmail';";
	$result= mysql_fetch_array(mysql_query($sql));
	//print_r($result);
	$type = $result["position"];
	if((($type == "Internal VP") || ($type == "President")) && isset($_POST["memberEmail"])){
		$userEmail = $_POST["memberEmail"];
	}
	
	$sql = "SELECT * FROM `attends` left join event on attends.eventNo=event.eventNo WHERE memberID='$userEmail' AND didAttend =1 AND event.semester='$CUR_SEM';";
	$attendedEvents = mysql_query($sql);
	$attendedNumber = mysql_num_rows($attendedEvents);
	
	
	$sql = "SELECT * FROM attends left join event on attends.eventNo=event.eventNo WHERE eventID=name AND memberID='$userEmail' AND event.semester='$CUR_SEM' order by callTime asc;";
	$allEvents = mysql_query($sql);
	$totalNumber = mysql_num_rows($allEvents);
	$reduction = 0;
	$html = "";
	//$lighter=" class = 'lighter' ";//make striped attendance history table
	while($row = mysql_fetch_array($allEvents)){
		if(time() > strtotime($row["callTime"])){//if event has occurred yet
			$typeNo = $row["type"];
			$sql3 = "SELECT * FROM `eventType` WHERE typeNo=$typeNo;";
			$result3 = mysql_fetch_array(mysql_query($sql3));
			$typeStr = $result3["typeName"];
			
			if(($row["shouldAttend"] == "1") && ($row["didAttend"] != "1")){
				$reduction = -1*intVal($row["pointValue"]);
			}
			if(($row["didAttend"] == "1") && ($typeStr == "Volunteer Gig")){
				$reduction = intVal($row["pointValue"]);
				//echo "point value: ".$row["pointValue"]." and intval: $reduction";
			}
			if($row['minutesLate'] != "0"){
				//minutes late divided by total minutes == fraction of time missed
				//fraction of time missed multiplied by number of points it's valued at
				if(getEventType($typeNo) == "Rehearsal"){
					$reduction = -1.0*(floatval($row['minutesLate'])/110.0)*10;
				}
				if(getEventType($typeNo) == "Sectional"){
					$reduction = -1.0*(floatval($row['minutesLate'])/50.0)*5;
				}
				if(getEventType($allEvents['type']) == "Tutti"){

				}
				if(getEventType($allEvents['type']) == "Volunteer"){

				}
				//$reduction = -1*intval($row['minutesLate']);
			}
			if($grade + $reduction > 100) {
				$reduction = 100-$grade;
			} 

			$grade = $grade + $reduction;
			$grade = $grade > 100 ? 100 : $grade;
			$attended = ($row["didAttend"] == "1" ? "<i class='icon-ok'></i>" : "<i class='icon-remove'></i>");
			$shouldAttend = ($row["shouldAttend"] == "0" ? "<i class='icon-remove'></i>" : "<i class='icon-ok'></i>");
			if($reduction < 0 && ($row['minutesLate'] != "0")){
				$reduction = "<strong>".$reduction."</strong> <p><small>".$row['minutesLate']." mins late</small></p>";
			}
			else{
				$reduction = ($reduction != 0) ? "<strong>".$reduction."</strong>" : "";
			}
			$html = $html. '
				<tr align="center"'.$lighter.'>
					<td >'.$row["eventID"].'</td>
					<td>'.$shouldAttend.'</td>
					<td>'.$attended.'</td>
					<td>'.$reduction.'</td>
				</tr>
				';
			$reduction = 0;
			/*if($lighter==''){$lighter = " class = 'lighter' ";}
			else{$lighter='';}*/
		}//end if event has occurred yet
	}
	
	$html =  '
		<h3>Score: '.$grade.'</h3>
		<table width="100%" id="defaultSidebar" class="table no-highlight table-bordered every-other">
		<thead>
			<th><span class="heading">Event</span></th>
			<th><span class="heading">Should have attended?</span></th>
			<th><span class="heading">Did attend?</span></th>
			<th><span class="heading">Point Change</span></th>
		</thead>
		'.$html;
	
	echo $html.'
		</table>
		';
	echo '</div>';
}

function actionOptions($userEmail){
	$sql = 'SELECT * FROM `member` WHERE email="'.$userEmail.'";';
	$result = mysql_fetch_array(mysql_query($sql));
	$type = $result["position"];
	$officerOptions = '';
	if(($type == "Internal VP") || ($type == "President")){
		$officerOptions = $officerOptions.'<li>
				 		<a href="#absenceRequest">See Absence Requests</a>
				 	</li>';
		$officerOptions = $officerOptions.'<li>
				 		<a href="#attendance">See All Attendance History</a>
				 	</li>';
	}
	if(($type != "Section Leader") && ($type != "Member")){
		$officerOptions = $officerOptions.'<li>
				 		<a href="#addEvent">Add Event</a>
				 	</li>
				 	<li>
				 		<a href="#addAnnouncement">Make an Announcement</a>
				 	</li>
				 	<li>
				 		<a href="../timeMachine">Look at Past Semesters</a>
				 	</li>';
	}
	if(($type != "Member") && ($type != "Section Leader")){
		$officerOptions = $officerOptions.'<li>
				 		<a href="#editMembers">Edit Members</a>
				 	</li>';
	}
	
	echo $officerOptions;
}

/**
* $scroll tell whether you want scroll to the bottom after this call (only used on the initial call, when the page loads)
*/
function loadChatbox($scroll){
	if(!isset($_COOKIE['email'])){
		loginBlock();
		return;
	}
	$lastSender='';
	$html='
	<div id="chatboxMessages">
	<table id="chatboxMessagesTable" class="table no-highlight">';
	$lighter = " class = 'lighter' ";
	$sql='SELECT * FROM `chatboxMessage` ORDER BY timeSent asc;';
	$results = mysql_query($sql);
	while($row = mysql_fetch_array($results)){
		if($lastSender == $row["sender"]){
			$sender = '';
		}
		else{
			$sender = $row["sender"];
			$sql = "SELECT * FROM `member` WHERE email='".$sender."';";
			$result= mysql_fetch_array(mysql_query($sql));
			$sender = $result["prefName"]." ".substr($result['lastName'], 0, 1).": ";
			$timeInt = strtotime($row["timeSent"]);
			$time = date("H:i", $timeInt);
			$day = date("M d", $timeInt);
		}
		$contents = $row["contents"];
		$messageID = $row['messageID'];
		/*if(strpos($contents, 'www.') === 0){
			$contents = '<a href="'.$contents.'" target="_blank">surprise link</a>';
		}
		if(strpos($contents, 'http://') === 0){
			$contents = '<a href="'.$contents.'" target="_blank">surprise link</a>';
		}*/
		
		$temp = '';
		$words = explode(' ', $contents);
		foreach($words as $value){
			$link = '';
			if((strpos($value, 'www.') === 0) || (strpos($value, 'http://') === 0) || (strpos($value, 'https://') === 0)){
				$link = $value;
				$domain = explode('/', $link);
				if(($domain[0] !== "http:") && ($domain[0] !== "https:")){
					$domainDisplay = $domain[0];
				}
				else{
					$domainDisplay = $domain[2];
				}
				//get just 'youtube' or 'imgur'
				//$domainBits = explode('.', $domainDisplay);
				
				$value = '<a href="'.$link.'" target="_blank">'.$link.'</a>';
			}
			if(strpos($link, '.jpg') || strpos($link, '.gif') || strpos($link, '.png')){
				$value = '<div class="btn" onclick="showChatboxImage(this);">show image</div><img class="chatboxImage" src="'.$link.'" onclick="hideChatboxImage(this);"/>';
			}
			$temp .= ' '.$value;
		}
		$contents = $temp;
		
		
		//this goes backwards. the first sql result is the last (most recent) one that appears onscreen. builds from the bottom up.
		$html = $html."
			<tr ".$lighter.">
				<td>
					<span class='chatboxTimestamp'><span>".$day." </span>
					<span>".$time."</span></span>
				</td>
				<td>
					<dl class='dl-horizontal'>
						<dt>
							<span class='chatboxSenderName'>".$sender."</span>
						</dt>
						<dd>
							<span data-messageID='".$messageID."' class='chatboxMessage'>".$contents."</span>
						</dd>
					</dl>
				</td>
			</tr>
		";
		//put in a submit button	
		$lastSender = $row["sender"];
		if($lighter==''){$lighter = " class = 'lighter' ";}
		else{$lighter='';}
	}
	$html.="
		<tr ".$lighter.">
				<td>
					<span class='chatboxTimestamp'><span></span>
					<span>now</span></span>
				</td>
				<td>
					<dl class='dl-horizontal'>
						<dt>
							<span class='chatboxSenderName'>You:</span>
						</dt>
						<dd>
							<div class='control-group form-inline'>
								<input type='text' id='shoutBox' />
								<div class='btn btn-primary' id='shoutButton'>shout</div>
							</div>
						</dd>
					</dl>
				</td>
			</tr>
		</table>
		</div>"; //make it all the same table so everything lines up

	//if this is the first time loading the page
	if($scroll=='1'){
		//wrap the whole page in a 'scrolldiv',  and have javascript can scroll to the bottom
		$html = "
		<div id='scrolldiv'>
			$html
		</div>

		<script type='text/javascript'>
			window.scrollBy(0, $('#scrolldiv').height());
		</script>";

	}
	echo $html;
}

function fullNameFromEmail($email) {
	return firstNameFromEmail($email) . " " . lastNameFromEmail($email);
}

function firstNameFromEmail($email){
	$sql = "SELECT firstName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	return $result["firstName"];
}

function prefNameFromEmail($email){
	$sql = "SELECT prefName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	return $result["prefName"];
}

function lastNameFromEmail($email){
	$sql = "SELECT lastName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	return $result["lastName"];
}

function prefFullNameFromEmail($email){
	return prefNameFromEmail($email).' '.lastNameFromEmail($email);
}

function positionFromEmail($userEmail){
	$sql = "SELECT * FROM `member` WHERE email='$userEmail';";
	$result= mysql_fetch_array(mysql_query($sql));
	//print_r($result);
	$type = $result["position"];
	return $type;
}

function emailFromPosition($position){
	//this should be done with numbers!
	$sql = "SELECT email FROM member WHERE position='$positon';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['email'];
}

function passengerSpots($email){
	$sql = "SELECT passengers FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['passengers'];
}

function livesAt($email){
	$sql = "SELECT location FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['location'];
}

function phoneNumber($email){
	$sql = "SELECT phone FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['phone'];
}

function isInClass($email){
	$sql = "SELECT registration FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	if($result['registration'] == '1'){
		return true;
	}
	else{
		return false;
	}
}

function getNumUnreadMessages($email) {
	$sql = "select sum(newMessages) from convoMembers where email='$email'";
	$res = mysql_fetch_array(mysql_query($sql));
	$newMessages = $res['sum(newMessages)'];
	return $newMessages;
}

function loginBlock(){
$html = '
	<div class="span3 block">
		<form class="form-inline" action="php/checkLogin.php" method="post">
		  <input type="text" class="input-medium" id="signInEmail" placeholder="gburdell3@gatech.edu" name="email" />
		  <input type="password" class="input-medium" id="signInPassword" placeholder="password" name="password" />
		  <button type="submit" value="Sign In" class="btn">Sign in</button>
		</form>
		<a href="#forgotPassword">Forgot Password?</a>
	</div>
';
echo $html;
}

//Grab all events for a user that they should attend for the current semester
function shouldAttendEvents($userEmail, $type='allEvents'){
	$CUR_SEM = getCurrentSemester();
	$t = '';
	switch($type) {
		case "rehearsal":
			$t = ' AND type = 1 ';
			break;
		case "sectional":
			$t = ' AND type = 2 ';
			break;
		case "volunteer":
			$t = ' AND type = 3 ';
			break;
		case "tutti":
			$t = ' AND type = 4 ';
			break;
	}

	if($userEmail == 'cernst3@gatech.edu' || $userEmail == 'ameloan3@gatech.edu'){
		if($t !== ''){$t = substr($t, 4);}
		else{$t='type>0';}
		$sql = "select * from (
		select callTime as time, name as occurrence, event.eventNo, semester 
		from event where $t ORDER BY time DESC)
		as res where semester='$CUR_SEM'";
	}
	else{
		$sql = "select * from (
		select callTime as time, name as occurrence, event.eventNo, semester 
		from event, attends
		where memberID='$userEmail' AND event.eventNo=attends.eventNo $t ORDER BY time DESC)
		as res where semester='$CUR_SEM'";
	}
	$results = mysql_query($sql);
	return $results;
}

function eventExtras(){
	$html = '
		<div class="block span6" id="eventDetails">
			<p>select an event</p>
		</div>
	';
	echo $html;
}

function getEventDetails($eventNo){
	$sql = "SELECT * FROM `event` WHERE eventNo=$eventNo;";
	$results = mysql_fetch_array(mysql_query($sql));
	return $results;
}

function getGigDetails($eventNo){
	$sql = "SELECT * FROM `gig` WHERE eventNo=$eventNo;";
	$results = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $results;
}

function getEventType($number){
	$sql = "SELECT typeName FROM `eventType` WHERE typeNo=$number;";
	$results = mysql_fetch_array(mysql_query($sql));
	return $results['typeName'];
}

function getEventName($eventNo){
	$eventSql = "SELECT name from `event` where eventNo='$eventNo'";
	$eventResults = mysql_fetch_array(mysql_query($eventSql));
	return $eventResults['name'];
}

function getEventTypeLabelClass($number){
	if($number == '1'){
		//rehearsal
		$class = 'label-info';
		return $class;
	}
	if($number == '2'){
		//sectional
		$class = 'label-success';
		return $class;
	}
	if($number == '3'){
		//tutti
		$class = 'label-warning';
		return $class;
	}
	if($number == '4'){
		//volunteer
		$class = 'label-important';
		return $class;
	}
}

function labelArea($type){
	$html = '<span class="label '.getEventTypeLabelClass($type).'">'.getEventType($type).'</span>';
	return $html;
}

function buttonArea($eventNo, $typeNumber){
	$sql = "SELECT * FROM `attends` WHERE eventNo=$eventNo AND memberID='".$_COOKIE['email']."';";
	$results = mysql_fetch_array(mysql_query($sql));
	//not confirmed
	$attendingDisplay = (($results['shouldAttend'] == '1') ? "<span class='label'>attending</span>" : '<span class="label">not attending</span>');

	//if it s a volunteer gig, give them the opportunity to change their choice later
	if($typeNumber == '3')
		$attendingDisplay.="<div><br><div class='btn btn-toggle'>I changed my mind</div></div>";
	if($results['confirmed'] == '0'){
		if($typeNumber == '3'){
			//not confirmed volunteer gig
			$html = '<div class="btn btn-primary btn-confirm" style="width:90%;">I will attend</div> <div class="btn btn-warning btn-deny" style="width:90%;">I won\'t attend</div>';
		}
		else{
			//not confirmed, not volunteer gig
			$html = '<div class="btn btn-confirm">Confirm I\'ll Attend</div>';
		}
	}
	else{
		//confirmed gig
		$html = $attendingDisplay;
	}
	return $html;
}

function requestAbsenceButton($eventNo){
	$absenceRequest = getAbsenceRequest($eventNo, $_COOKIE['email']);
	if($absenceRequest['state'] == 'pending'){
		return '<td><span class="label label-warning">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	}
	if($absenceRequest['state'] == 'confirmed'){
		return '<td><span class="label label-success">absence request '.$absenceRequest['state'].'</span></td><td></td>';
	}
	if($absenceRequest['state'] == 'denied'){
		return '<td><span class="label label-important">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	}
	else{
		$eventDetails = getEventDetails($_SESSION['eventNo']);
		$callTime = strtotime($eventDetails['callTime']);
		if($callTime > time()){
			return '<div class="btn">request absence</div>';
		}
	}
	//print_r($absenceRequest);
}

function getAbsenceRequest($eventNo, $person){
	$sql = "SELECT * FROM `absencerequest` WHERE eventNo=$eventNo AND memberID='$person';";
	$results = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $results;
}

function isOfficer($email){
	//this should be done with numbers...
	if(positionFromEmail($email) == "Manager" ||
	positionFromEmail($email) == "Ombudsman" ||
	positionFromEmail($email) == "Internal VP" ||
	positionFromEmail($email) == "External VP" ||
	positionFromEmail($email) == "Treasurer" ||
	positionFromEmail($email) == "President"){
		return true;
	}
	else{
		return false;
	}
}

function getTreasurerEmail() {
	$sql = "SELECT email FROM member WHERE position='Treasurer'";
	$res = mysql_fetch_array(mysql_query($sql));
	return $res['email'];
}

function getCarpoolDetails($carpoolId){
	$sql = "SELECT * FROM `ridesin` WHERE carpoolID=$carpoolId;";
	$result = mysql_query($sql);
	return $result;
}

function shouldAttend($email, $eventNo){
	$sql = "SELECT shouldAttend FROM attends WHERE memberID='$email' AND eventNo=$eventNo;";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['shouldAttend'] == 0 ? false : true;
}

function isConfirmed($email, $eventNo){
	$sql = "SELECT confirmed FROM attends WHERE memberID='$email' AND eventNo=$eventNo;";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['confirmed'] == 0 ? false : true;
}

function encrypt2($string) {
	return base64_encode($string ^ "12345678900987654321qwertyuiopasdfghjklzxcvbnm,.");
}

function decrypt2($string) {
	return base64_decode($string) ^ "12345678900987654321qwertyuiopasdfghjklzxcvbnm,.";
}

function encrypt($string, $key) {
  $result = '';
  for($i=0; $i<strlen($string); $i++) {
    $char = substr($string, $i, 1);
    $keychar = substr($key, ($i % strlen($key))-1, 1);
    $char = chr(ord($char)+ord($keychar));
    $result.=$char;
  }
  return base64_encode($result);
}

function sendMessageEmail($to, $from, $message, $subjectField = "New Absence Request"){
	$fromEmail = $from;
	$from = prefNameFromEmail($from)." ".lastNameFromEmail($from);
	if(strpos($to, ',')){//being sent to multiple people
		$toField = $to;
	}
	else{//being sent to one person
		$toField = prefNameFromEmail($to)." ".lastNameFromEmail($to)."<".$to.">"; //make it format: Chris Ernst <cernst3@gatech.edu>
	}
	if(!$subjectField){$subjectField = 'Message from '.$from.'!';} //if there's no subject, it's a message...maybe.
	$messageField = '
	<html>
	<head>
		<style>
			.container{
				font-family:"lucida grande",tahoma,verdana,arial,sans-serif;
				height:100%;
				padding:5px;
				margin-bottom:3px;
			}
			body{
				background:rgb(179,179,179);
			}
			h1{
				width:100%;
				margin:auto;
				text-align:center;
				text-decoration:underline;
				margin-bottom:3px;
			}
			.from{
				width:100%;
				margin:auto;
				text-align:center;
			}
			.message{
				border-radius:2px;
				background-color:rgba(255,255,255, .7);
				padding:5px;
				border:1px solid rgba(0, 0, 0, 0.1);
				box-shadow:0 4px 7px rgba(0,0,0, .4);
				margin:5px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<p class="message">'.$message.'</p>
			<p style="float:right;">-'.$from.'</p>
		</div>
	</body>
	</html>
	';
	//reply-to isn't working, but that seems to be alright because the form field is working.
	$headers = 'Content-type:text/html;\r\n
				Reply-To: '.$from.' <'.$fromEmail.'>' . "\r\n" .
				'From: '.$from.' <'.$fromEmail.'>' . "\r\n" .
				'X-Mailer: PHP/' . phpversion();
	mail($toField, $subjectField, $messageField, $headers);
	//echo $toField." ".$subjectField." ".$messageField." ".$headers;
}



function eventEmail($eventNo,$typeName){

	if($typeName=="Volunteer Gig" || $typeName=="Tutti Gig"){
		$emailSql = "SELECT * from `member` WHERE confirmed=1";

		//Uncomment the next line, and only developers will recieve emails
		//$emailSql = "SELECT * from `member` WHERE email='ameloan3@gatech.edu' or email='awesome@gatech.edu' or email='thope6@gatech.edu' or email='cernst3@gatech.edu'";
		
		$emailToString='';
		$emailResults = mysql_query($emailSql);
		
		$eventSql = "SELECT * from `event` where eventNo='$eventNo'";
		$eventResults = mysql_fetch_array(mysql_query($eventSql));
		$eventName = $eventResults['name'];
		$eventTypeNo = $eventResults['type'];
		$eventTime = $eventResults['callTime'];
		$eventReleaseTime = $eventResults['releaseTime'];
		$eventComments = $eventResults['comments'];
		$eventLocation = $eventResults['location'];
		$eventUniform =  $eventResults['uniform'];
		
		$eventTime = strtotime($eventTime);
		$eventTimeDisplay = date("D, M d @ g:i a", $eventTime);

		$eventReleaseTime = strtotime($eventReleaseTime);
		$eventReleaseTimeDisplay = date("D, M d @ g:i a", $eventReleaseTime);
		
		$typeSql = "SELECT * from `eventType` where typeNo=$eventTypeNo";
		$typeResults = mysql_fetch_array(mysql_query($typeSql));
		$eventType = $typeResults['typeName'];

		$redirectURL = "$BASEURL/php/fromEmail.php";
		
		while($emailRow = mysql_fetch_array($emailResults)){
			$userEmail = $emailRow["email"];
			$userPrefName = $emailRow["firstName"];
			$userLastName = $emailRow["lastName"];
			$emailToString = "$userPrefName $userLastName <$userEmail>";
			$headers = 'Content-type:text/html;From: gleeclub_officers@lists.gatech.edu' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
			
			$message='
				<html>
				<head>
					<style>
						.container{
							font-family:"lucida grande",tahoma,verdana,arial,sans-serif;
							height:100%;
							padding:5px;
							margin-bottom:3px;
						}
						h1{
							width:100%;
							margin:auto;
							text-align:center;
							text-decoration:underline;
							margin-bottom:3px;
						}
						.newsfeeditem{
							background-color:rgb(94,94,192);
							overflow:hidden;
							cursor:pointer;
							margin-bottom:.5%;
							box-shadow: 0 0 2px 3px rgb(26,26,192) inset;
						}
						.type{
							/*font-size: 1.6em;*/
							/*font-weight:bold;*/
							width:14%;
							text-align:left;
							padding-left:1%;
						}
						.name{
							width:26%;
							text-align:center;
							font-size:1.2em;
							font-weight:bold;
							padding-left:1%;
						}
						.time{
							width:20%;
							text-align:center;
							padding-left:1%;
						}
						.button{
							width:20%;
							text-align:center;
						}
						.attending{
							width:20%;
							text-align:left;
							padding-left:1%;
							/*font-size: 1.6em;*/
						}
						.mybutton{
							/*steal the facebook font stack!*/
							font-family:"lucida grande",tahoma,verdana,arial,sans-serif;
							font-size:.8em;
							width:40%;
							color:black;
							background-color: hsl(201, 100%, 30%);
							background-repeat: repeat-x;
							background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, hsl(240, 44%, 60%)), color-stop(100%, hsl(240, 44%, 40%)));
							border-color: hsl(201, 100%, 30%) hsl(201, 100%, 30%) hsl(201, 100%, 25%);
							-webkit-font-smoothing: antialiased;
							border: 1px solid #444;
							border-radius:3px;
						}
						.mybutton:hover{
							background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, hsl(240, 44%, 60%)), color-stop(100%, hsl(240, 44%, 30%)));
							cursor:pointer;
						}
						.mybutton:active{
							position:relative;
							top:1px;
							background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, hsl(240, 44%, 50%)), color-stop(100%, hsl(240, 44%, 70%)));
							cursor:pointer;
						}
						.spacer{
							width:20%;
						}
					</style>
				</head>
				<body>
					<div class="container">
						<h1>New Event Added</h1>
						<p>A new event has been added and you have been marked as attending.</p>
						<div class="newsfeeditem" id="type"'."$eventNo".'">
						<p>';

		if($typeName=="Volunteer Gig"){
			$message.='
						<form name="willattend" action="'.$redirectURL.'" method="get" target="_blank">
							<table width="100%" style="border-collapse:collapse">
									<tr align="center"><td class="type">'."$eventType".'</td></tr>
									<tr align="center"><td class="time">'."$eventTimeDisplay".'</td></tr>
									<tr align="center"><td class="name">'."$eventName".'</td></tr>
									<tr align="center"><td class="attending">Location: '."$eventLocation".'<br/>---</td></tr>
									<tr align="center"><td class="attending">Uniform: '."$eventUniform".'<br/>---</td></tr>
									<tr align="center"><td class="attending">Estimated Release Time: '."$eventReleaseTimeDisplay".'<br/>---</td></tr>
									<tr align="center"><td class="attending">Comments: '."$eventComments".'</td></tr>
									<tr align="center"><td class="button" id="'."$eventNo".'buttons">
										<input type="hidden" value="'."$eventNo".'" name="id" />
										<input type="hidden" value="'."$userEmail".'" name="user" />
										<input type="submit" value="I will attend" class="mybutton" id="'."$eventNo".'willattend" name="willAttend" target="_blank" /></td>
									</tr>
							</table>
						</form>
						<form name="wontattend" action="'.$redirectURL.'" method="get" target="_blank">
							<table width="100%" style="border-collapse:collapse">
								<tr align="center">
									<td class="button" id="'."$eventNo".'buttons">
									<input type="hidden" value="'."$eventNo".'" name="id" />
									<input type="hidden" value="'."$userEmail".'" name="user" />
									<input type="submit" value="I won\'t attend" class="mybutton" id="'."$eventNo".'wontattend" name="willAttend" target="_blank" />		
									</td>
								</tr>
							</table>
						</form>';
		}
		if($typeName=="Tutti Gig"){
			$message.='
						<form name="willattend" action="'.$redirectURL.'" method="get" target="_blank">
							<table width="100%" style="border-collapse:collapse">
									<tr align="center"><td class="type">'."$eventType".'</td></tr>
									<tr align="center"><td class="time">'."$eventTimeDisplay".'</td></tr>
									<tr align="center"><td class="name">'."$eventName".'</td></tr>
									<tr align="center"><td class="attending">Location: '."$eventLocation".'<br/>---</td></tr>
									<tr align="center"><td class="attending">Uniform: '."$eventUniform".'<br/>---</td></tr>
									<tr align="center"><td class="attending">Estimated Release Time: '."$eventReleaseTimeDisplay".'<br/>---</td></tr>
									<tr align="center"><td class="attending">Comments: '."$eventComments".'</td></tr>
									<tr align="center"><td class="button" id="'."$eventNo".'buttons">
										<input type="hidden" value="'."$eventNo".'" name="id" />
										<input type="hidden" value="'."$userEmail".'" name="user" />
										<input type="submit" value="Confirm I will attend" class="mybutton" id="'."$eventNo".'willattend" name="willAttend" target="_blank" /></td>
									</tr>
							</table>
						</form>';
		}
		$message.='
						</p>
						</div>
					</div>
				</body>
				</html>
			';
			mail($emailToString, "There's a New Glee Club Event!", $message, $headers);
		}
	}
}

function getConfirmedMembers(){
	$sql = 'SELECT * FROM `member` where confirmed=1;';
	$results = mysql_query($sql);
	return $results;
}
function getAllMembers(){
	$sql = 'SELECT * FROM `member` ORDER BY confirmed desc, lastName asc, firstName asc;';
	$results = mysql_query($sql);
	return $results;
}

function loadTodoBlock($userEmail){
	echo "<div id='todos'>";
	$sql = "SELECT * FROM `todoMembers` where memberID='$userEmail' ORDER BY todoID ASC;";
	$todos = mysql_query($sql);
	while ($row = mysql_fetch_array($todos, MYSQL_ASSOC)){
		$id = $row['todoID'];
		$sql2 = "SELECT * FROM `todo` WHERE id=$id;";
		$results = mysql_query($sql2);
		$text = mysql_fetch_array($results, MYSQL_ASSOC);
		$completed = ($text['completed'] == "1") ? "checked='yes'" : "";
		$text = $text['text'];
		echo "<div class='block'>
				<label class='checkbox'>
					<input $completed type='checkbox' id='$id'> $text
				</label>
			</div>";
		$completed='';
	}
	echo "</div>";
}

/**
* Returns attendance info about the event whose eventNo matches $eventNo in the form of rows
**/
function getEventAttendanceRows($eventNo){
	$sql = "select * from member where confirmed='1' order by lastName, firstName";
	$members = mysql_query($sql);

	$eventRows = "
	<tr class='topRow'>
		<td class='cellwrap'>Name</td>
		<td class='cellwrap'>Attended</td>
		<td class='cellwrap'>Minutes Late</td>
		<td class='cellwrap'>Toggle</td>
	</tr>";

	while($member=mysql_fetch_array($members)){
		$memberID = $member['email'];
		$firstName = $member['firstName'];
		$lastName = $member['lastName'];
		$attendsID = "attends_".$memberID."_$eventNo";

		//the member's name
		$eventRows.= "
		<tr id='$attendsID'>
			<td id='$attendsID"."_name' class='data'>$firstName $lastName</td>";

		$sql = "select didAttend from attends where memberID='$memberID' and eventNo='$eventNo'";
		$attendses = mysql_query($sql);

		//make sure the member has some attends relationships
		if(mysql_num_rows($attendses)!=0){
			while($attends=mysql_fetch_array($attendses)){
				$didAttend = $attends['didAttend'];

				//did the person attend
				if($didAttend=="1")
					$eventRows.="
						<td id='$attendsID_did' align='center' class='data'><font color='green'>Yes</font></td>";
				else
					$eventRows.="
						<td id='$attendsID_did' align='center' class='data'><font color='red'>No</font></td>";	

				//minutes late
				$minutesLate = getMinutesLate($memberID, $eventNo);
				$eventRows .= "<td align='center' class='data'>
								<div class='control-group'>
									<input type='text' placeholder='$minutesLate' class='input-mini' id='$memberID-minutesLate' />
								</div>
							</td>";

				//add a button to change the current status
				if($didAttend=="1")
					$eventRows.="
					<td align='center' id='$attendsID_toggle' class='data'><button type='button' class='btn' onclick='setDidAttendEvent(\"$eventNo\",\"$memberID\",\"0\")'>Wasn't there</button></td>";
				else
					$eventRows.="
					<td align='center' id='$attendsID_toggle' class='data'><button type='button' class='btn' onclick='setDidAttendEvent(\"$eventNo\",\"$memberID\",\"1\")'>Was there</button></td>";		

				//terminate the row with a space to ensure at least two lines per row
				$eventRows.="
					<td><br><br></td>
				</tr>";
			}
		}
		//if the member was not ever meant to go (has no attends relationship)
		else{
			$eventRows.="
				<td align='center' class='data'>No</td>
				<td align='center' id='$attendsID_toggle'><button type='button' onclick='setDidAttendEvent(\"$eventNo\",\"$memberID\",\"1\")'>Was there</button></td>
				<td><br><br></td>
			</tr>";
		}
	}
	return $eventRows;
}

function getMinutesLate($memberID, $eventNo){
	$sql = "SELECT minutesLate from `attends` WHERE eventNo=$eventNo AND memberID='$memberID';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['minutesLate'];
}

?>
