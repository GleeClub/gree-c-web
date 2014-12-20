<?php
//it would seem you cannot connect to the database from outside a function and inside a function
require_once('functions.php');
$userEmail = mysql_real_escape_string($_COOKIE['email']);

if(isset($_COOKIE['email'])) {
	$text = $_POST['text'];
	$sql = "INSERT INTO  `announcement` (`announcementNo`,`memberID`,`timePosted`,`announcement`) VALUES (NULL ,'$userEmail', NOW( ),'".mysql_real_escape_string($text)."');";
	mysql_query($sql);

	$sql = "select * from member where email='$userEmail'";
	$user = mysql_fetch_array(mysql_query($sql));
	$firstName = $user['firstName'];
	$prefName = $user['prefName'];
	$lastName = $user['lastName'];
	$position = $user['position'];

	$recipient = "gleeclub@lists.gatech.edu";
	$subject = "Important message from your $position!";

	$headers = 'Reply-To: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\r\n" .
				'From: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\r\n" .
				'X-Mailer: PHP/' . phpversion();
	mail($recipient, $subject, $text, $headers);
	//sendMessageEmail($recipient, $from, $text, $subject);
}

?>