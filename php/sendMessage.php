<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
$messageText = $_POST['message'];

$SQLmessage = mysql_real_escape_string($messageText);
$to = $_SESSION["otherPerson"];

//for debug send all emails to me:
//$to = "cernst3@gatech.edu";

$sql = "INSERT INTO message (sender, recipient, contents) VALUES ('$userEmail', '$to', '$SQLmessage');";
//echo $sql;
mysql_query($sql);

//if chris or drew, send an email
if($to == "cernst3@gatech.edu" || $to == "ameloan3@gatech.edu"){
	$sql = "SELECT * FROM `member` WHERE email='".$userEmail."';";
	$result = mysql_fetch_array(mysql_query($sql));
	$from = $result["prefName"]." ".$result["lastName"];
	
	$sql = "SELECT * FROM `member` WHERE email='".$to."';";
	$result = mysql_fetch_array(mysql_query($sql));
	$toPerson = $result["prefName"]." ".$result["lastName"];
	
	$toField = prefNameFromEmail($to)." ".lastNameFromEmail($to)."<".$to.">"; //make it format: Chris Ernst <cernst3@gatech.edu>
	$subjectField = 'Gree-C-Web Message from '.$from.'!';
	
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
				padding:6px;
				background-color: rgb(240,240,240);
			}
		</style>
	</head>
	<body>
		<div class="container">
			<h1>Gree-C-Web Message!</h1>
			<p class="message">'.$messageText.'</p>
			<p>-'.$from.'</p>
		</div>
	</body>
	</html>
	';
	
	//reply-to isn't working, but that seems to be alright because the form field is working.
	$headers = 'Content-type:text/html;\n
				Reply-To: '.$from.' <'.$userEmail.'>' . "\n" .
				'From: '.$from.' <'.$userEmail.'>' . "\n" .
				'X-Mailer: PHP/' . phpversion();
	
	mail($toField, $subjectField, $messageField, $headers);
}


echo $_SESSION['otherPerson'];

?>
