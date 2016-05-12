<?php
require_once('functions.php');
$messageText = $_POST['message'];
$SQLmessage = mysql_real_escape_string($messageText);
$to = $_POST["otherPerson"];

//for debug send all emails to me:
//$to = "cernst3@gatech.edu";

$sql = "INSERT INTO message (sender, recipient, contents) VALUES ('$USER', '$to', '$SQLmessage');";
//echo $sql;
mysql_query($sql);

//if chris or drew, send an email
if($to == "cernst3@gatech.edu" || $to == "ameloan3@gatech.edu"){
	$sql = "SELECT * FROM `member` WHERE email='".$USER."';";
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
				Reply-To: '.$from.' <'.$USER.'>' . "\n" .
				'From: '.$from.' <'.$USER.'>' . "\n" .
				'X-Mailer: PHP/' . phpversion();
	
	mail($toField, $subjectField, $messageField, $headers);
}


echo $to;

?>
