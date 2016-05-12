<?php
//it would seem you cannot connect to the database from outside a function and inside a function
require_once('functions.php');

if (! $USER) die("Access denied");
if (! $CHOIR) die("Choir not set");
$row = mysql_fetch_array(mysql_query("select `admin`, `list` from `choir` where `id` = '$CHOIR'"));
$sender = $row['admin'];
$recipient = $row['list'];

$text = $_POST['text'];
$sql = "INSERT INTO  `announcement` (`announcementNo`, `choir`, `memberID`,`timePosted`,`announcement`) VALUES (NULL, '$CHOIR', '$USER', NOW( ),'".mysql_real_escape_string($text)."');";
mysql_query($sql);
$position = positions($USER)[0];

$subject = "Important message from your $position!";

$headers = "Reply-To: $sender\n" .
			"From: $sender\n" .
			'X-Mailer: PHP/' . phpversion();
mail($recipient, $subject, $text, $headers);
//sendMessageEmail($recipient, $from, $text, $subject);
?>
