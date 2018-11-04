<?php
require_once('functions.php');
if (! $CHOIR) die("Choir not set");
$row = query("select `admin`, `list` from `choir` where `id` = ?", [$CHOIR], QONE);
if (! $row) die("Invalid choir");
$recipient = $row['admin'];
$prefix = "<html><head></head><body>";
$suffix = "</body></html>";
$headers = 'Content-type:text/html; charset=utf-8' . "\n" .
	'From: $recipient' . "\n" .
	'X-Mailer: PHP/' . phpversion();

if (! isset($_POST['id'])) die("No ID specified");
$id = $_POST['id'];
$result = query("select `name`, `private` from `minutes` where `id` = ?", [$id], QONE);
if (! $result) die("Failed to fetch minutes with ID " . $id);
$message = $prefix . $result['private'] . "<br><br>View these minutes online at $BASEURL/#minutes:$id<br>" . $suffix;
$subject = "Minutes for " . $result['name'];

if (! mail($recipient, $subject, $message, $headers)) die("Failed to send the email");
echo "OK";
?>
