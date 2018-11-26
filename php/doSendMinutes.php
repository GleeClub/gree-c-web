<?php
require_once('functions.php');
if (! $CHOIR) err("Choir not set");
$row = query("select `name`, `admin` from `choir` where `id` = ?", [$CHOIR], QONE);
if (! $row) err("Invalid choir");
$recipient = $row["name"] . " Officers <" . $row['admin'] . ">";
$prefix = "<html><head></head><body>";
$suffix = "</body></html>";
$headers = 'Content-type:text/html; charset=utf-8' . "\n" .
	'From: $recipient' . "\n" .
	'X-Mailer: PHP/' . phpversion();

if (! isset($_POST['id'])) err("No ID specified");
$id = $_POST['id'];
$result = query("select `name`, `private` from `minutes` where `id` = ?", [$id], QONE);
if (! $result) err("Failed to fetch minutes with ID " . $id);
$message = $prefix . $result['private'] . "<br><br>View these minutes online at $BASEURL/#minutes:$id<br>" . $suffix;
$subject = "Minutes for " . $result['name'];

if (! mail($recipient, $subject, $message, $headers)) err("Failed to send the email");
echo "OK";
?>
