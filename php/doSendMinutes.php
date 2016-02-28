<?php
require_once('functions.php');
$choir = getchoir();
if (! $choir) die("Choir not set");
$row = mysql_fetch_array(mysql_query("select `admin`, `list` from `choir` where `id` = '$choir'"));
$recipient = $row['admin'];
$prefix = "<html><head></head><body>";
$suffix = "</body></html>";
$headers = 'Content-type:text/html; charset=utf-8' . "\n" .
	'From: $recipient' . "\n" .
	'X-Mailer: PHP/' . phpversion();

if (! isset($_POST['id'])) die("No ID specified");
$id = mysql_real_escape_string($_POST['id']);
$query = mysql_query("select `name`, `private` from `minutes` where `id` = '$id'");
if (! $query) die("Query failed: " + mysql_error());
$result = mysql_fetch_array($query);
if (! $result) die("Failed to fetch minutes with ID " . $id);
$message = $prefix . $result['private'] . "<br><br>View these minutes online at $BASEURL/#minutes:$id<br>" . $suffix;
$subject = "Minutes for " . $result['name'];

if (! mail($recipient, $subject, $message, $headers)) die("Failed to send the email");
echo "OK";
?>
