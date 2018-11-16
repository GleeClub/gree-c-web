<?php
require_once('functions.php');

if (! $USER) die("Access denied");
if (! $CHOIR) die("Choir not set");
$row = query("select `name`, `admin`, `list` from `choir` where `id` = ?", [$CHOIR], QONE);
if (! $row) die("Choir is invalid");
$sender = $row["name"] . " Officers <" . $row["admin"] . ">";
$recipient = $row["name"] . " <" . $row["list"] . ">";

$text = $_POST["text"];
query("insert into `announcement` (`announcementNo`, `choir`, `memberID`, `timePosted`, `announcement`) values (null, ?, ?, now(), ?)", [$CHOIR, $USER, $text]);
$position = positions($USER)[0];

$subject = "Important message from your $position!";

$headers = "Reply-To: $sender\n" .
			"From: $sender\n" .
			'X-Mailer: PHP/' . phpversion();
mail($recipient, $subject, $text, $headers);
//sendMessageEmail($recipient, $from, $text, $subject);
?>
