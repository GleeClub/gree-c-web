<?php
require_once('functions.php');
$recipient = 'gleeclub_officers@lists.gatech.edu';
$prefix = "<html><head></head><body>";
$suffix = "</body></html>";
$headers = 'Content-type:text/html; charset=utf-8' . "\n" .
	'From: $admin_email' . "\n" .
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
