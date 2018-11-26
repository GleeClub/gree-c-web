<?php

require_once('./functions.php');

function absenceEmail($recipient, $state, $event)
{
	global $CHOIR;
	//$to = prefNameFromEmail($recipient)." ".lastNameFromEmail($recipient)." <".$recipient.">"; //make it format: Chris Ernst <cernst3@gatech.edu>
	$subject = "Absence Request " . ucfirst($state);
	$msg = memberName($recipient, "pref") . ",<br><br> Your absence request for " . $event . " has been " . $state . ".<br><br>Glee Club Officers";
	$message = '
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
			)}
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
		<p class="message">'.$msg.'</p>
		</div>
	</body>
	</html>
	';
	if (! $CHOIR) err("Choir not set");
	$info = query("select `name`, `admin` from `choir` where `id` = ?", [$CHOIR], QONE);
	if (! $info) err("Invalid choir");
	$sender = $info["name"] . " Officers <" . $info["admin"] . ">";
	$headers = "Content-type: text/html\r\nX-Mailer: PHP/".phpversion()."\r\nReply-To: $sender";
	mail($recipient, $subject, $message, $headers);
}

if (! hasPermission("process-absence-requests")) err("<td align='center' colspan='7' class='data'>You don't have permission to do this.</td>");
if (! isset($_POST['eventNo'])) err("<td align='center' colspan='7' class='data' style='font-weight:bold'>Something went wrong. :0</td>");

$eventNo = $_POST["eventNo"];
$email = $_POST["email"];
$action = $_POST["action"];

//check which action you're doing, as this changes what you will se the state of the request and the shouldAtend field to
if ($action == "approve")
{
	$state = "confirmed";
	$shouldAttend = "0";
}
else if ($action == "deny")
{
	$state = "denied";
	$shouldAttend = "1";
}
else if ($action == "toggle")
{
	$reqstate = query("select `state` from `absencerequest` where `memberID` = ? and `eventNo` = ?", [$email, $eventNo], QONE);
	if (! $reqstate) err("Could not find absence request");
	if ($reqstate["state"] == "confirmed")
	{
		$state = "denied";
		$shouldAttend = "1";
	}
	else
	{
		$state = "confirmed";
		$shouldAttend = "0";
	}
}

//make the queries
query("update `absencerequest` set `state` = ? where `memberID` = ? and `eventNo` = ?", [$state, $email, $eventNo]);
query("update `attends` set `shouldAttend` = ?, `confirmed` = ? where `memberID` = ? and `eventNo` = ?", [$shouldAttend, 1, $email, $eventNo]);

// Notify the requester
$evname = query("select `name` from `event` where `eventNo` = ?", [$eventNo], QONE);
if (! $evname) err("Could not find event");
absenceEmail($email, $state, $evname["name"]);

//get the updated information to plug back into the row
$request = query("select  `absencerequest`.`eventNo` ,  `absencerequest`.`time` ,  `absencerequest`.`reason` ,  `absencerequest`.`replacement` ,  `absencerequest`.`memberID` ,  `absencerequest`.`state` ,  `event`.`callTime` , `event`.`name` ,  `member`.`firstName` ,  `member`.`lastName` from  `absencerequest` ,  `member` ,  `event` where  `absencerequest`.`eventNo` = ? and `event`.`eventNo` = ? and `absencerequest`.`memberID` = ? and `member`.`email` = ?", [$eventNo, $eventNo, $email, $email], QONE);
if (! $request) err("Could not find absence request");

$eventNo = $request["eventNo"];
$time = $request["time"];
$reason = $request["reason"];
$email = $request["memberID"];
$state = $request["state"];
$name = $request["firstName"]." ".$request["lastName"];
$eventName = $request["name"];
$replacement = "";

if ($request["replacement"] != "") $replacement = memberName($request["replacement"], "pref");

echo "
	<td align='left' class='data'>$time</td>
	<td align='left' class='data'>$eventName</td>
	<td align='left' class='data'>$name</td>
	<td align='left' class='data'>$reason</td>
	<td align='center' class='data'>$replacement</td>
	<td align='center' class='data'>$state</td>
	<td align='center' class='data'><button onclick='toggleRequestState(\"$eventNo\", \"$email\");'>Toggle</button></td>";
?>
