<?php
require_once('functions.php');

if (! $USER) err("Access denied");
if (! $CHOIR) err("Choir not set");
$row = query("select `name`, `admin`, `list` from `choir` where `id` = ?", [$CHOIR], QONE);
if (! $row) err("Choir is invalid");
$sender = $row["name"] . " Officers <" . $row["admin"] . ">";
$recipient = $row["name"] . " <" . $row["list"] . ">";

$text = $_POST["text"];
query("insert into `announcement` (`announcementNo`, `choir`, `memberID`, `timePosted`, `announcement`) values (null, ?, ?, now(), ?)", [$CHOIR, $USER, $text]);
$position = positions($USER)[0];

$subject = "Important message from your $position!";

$headers = "Content-type: text/html\n".
			"charset=UTF-8\n".
			"Reply-To: $sender\n" .
			"From: $sender\n" .
			'X-Mailer: PHP/' . phpversion();
if (query("select * from `emailSettings` where `id` = ? and `enabled` != '0'`", ["new-announcement"], QCOUNT) > 0) mail($recipient, $subject, $text, $headers);
?>
