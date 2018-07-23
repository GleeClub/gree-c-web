<?php

require_once('./functions.php');

function absenceEmail($recipient, $state, $event)
{
	global $CHOIR;
	//$to = prefNameFromEmail($recipient)." ".lastNameFromEmail($recipient)." <".$recipient.">"; //make it format: Chris Ernst <cernst3@gatech.edu>
	$subject = "Absence Request " . ucfirst($state);
	$msg = prefNameFromEmail($recipient) . ",<br><br> Your absence request for " . $event . " has been " . $state . ".<br><br>Glee Club Officers";
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
	if (! $CHOIR) die("Choir not set");
	$row = mysql_fetch_array(mysql_query("select `admin`, `list` from `choir` where `id` = '$CHOIR'"));
	$sender = $row['admin'];
	$headers = "Content-type: text/html\r\nX-Mailer: PHP/".phpversion()."\r\nReply-To: $sender";
	mail($recipient, $subject, $message, $headers);
}

if (! hasPermission("process-absence-requests") die("<td align='center' colspan='7' class='data'>You don't have permission to do this.</td>");
if (! isset($_POST['eventNo'])) die("<td align='center' colspan='7' class='data' style='font-weight:bold'>Something went wrong. :0</td>");

$eventNo = mysql_real_escape_string($_POST["eventNo"]);
$email = mysql_real_escape_string($_POST["email"]);
$action = mysql_real_escape_string($_POST["action"]);

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
	$sql = "select state from absencerequest where memberID='$email' and eventNo='$eventNo'";
	$query = mysql_query($sql);
	if (! $query) die(mysql_error());
	if (mysql_num_rows($query) == 0) die("Absence request not found");
	$request = mysql_fetch_array($query);
	if($request["state"] == "confirmed")
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
$sql = "update absencerequest set state='$state' where memberID='$email' and eventNo='$eventNo'";
if (! mysql_query($sql)) die(mysql_error());
$sql="update attends set shouldAttend='$shouldAttend',confirmed='1' where memberID='$email' and eventNo='$eventNo'";
if (! mysql_query($sql)) die(mysql_error());

// Notify the requester
$sql = "select `name` from `event` where `eventNo` = '$eventNo'";
$request = mysql_fetch_array(mysql_query($sql));
absenceEmail($email, $state, $request['name']);

//get the updated information to plug back into the row
$sql= "SELECT  `absencerequest`.`eventNo` ,  `absencerequest`.`time` ,  `absencerequest`.`reason` ,  `absencerequest`.`replacement` ,  `absencerequest`.`memberID` ,  `absencerequest`.`state` ,  `event`.`callTime` , `event`.`name` ,  `member`.`firstName` ,  `member`.`lastName` FROM  `absencerequest` ,  `member` ,  `event` WHERE  `absencerequest`.`eventNo`='$eventNo' AND `event`.`eventNo`='$eventNo' and `absencerequest`.`memberID`='$email' and `member`.`email`='$email'";
$request = mysql_fetch_array(mysql_query($sql));

$eventNo = $request['eventNo'];
$time = $request["time"];
$reason = $request["reason"];
$email = $request["memberID"];
$state = $request["state"];
$name = $request["firstName"]." ".$request["lastName"];
$eventName = $request["name"];
$replacement = "";

if ($request["replacement"] != "")
{
	$sql = "SELECT  `member`.`firstName` ,  `member`.`lastName` FROM  `member` WHERE `member`.`email`='" . $request["replacement"] . "'";
	$result = mysql_fetch_array(mysql_query($sql));
	$replacement =$result["firstName"]." ".$result["lastName"];
}

echo "
	<td align='left' class='data'>$time</td>
	<td align='left' class='data'>$eventName</td>
	<td align='left' class='data'>$name</td>
	<td align='left' class='data'>$reason</td>
	<td align='center' class='data'>$replacement</td>
	<td align='center' class='data'>$state</td>
	<td align='center' class='data'><button onclick='toggleRequestState(\"$eventNo\", \"$email\");'>Toggle</button></td>";
?>
