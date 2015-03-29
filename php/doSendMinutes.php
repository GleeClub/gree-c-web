<?php
require_once('functions.php');
$id = mysql_real_escape_string($_POST['id']);
$recipient = 'gleeclub_officers@lists.gatech.edu';
$prefix = "<html><head></head><body>";
$suffix = "</body></html>";
$headers = 'Content-type:text/html; charset=utf-8' . "\n" .
	'Reply-To: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\n" .
	'From: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\n" .
	'X-Mailer: PHP/' . phpversion();

$query = mysql_query("select `name`, `private` from `minutes` where `id` = '$id'");
if (! $query) die("Query failed: " + mysql_error());
$result = mysql_fetch_array($query);
$message = $prefix . $result['private'] . "<br><br>View these minutes online at http://gleeclub.gatech.edu/buzz/#minutes:$id<br>" . $suffix;
$subject = "Minutes for " . $result['name'];

if (! mail($recipient, $subject, $message, $headers)) die("Failed to send the email");
echo "OK";
?>
