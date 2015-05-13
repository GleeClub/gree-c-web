<?php
require_once('functions.php');
$userEmail = getuser();
$officer = isOfficer($userEmail);
$uber = (positionFromEmail($userEmail) == 'President' || positionFromEmail($userEmail) == 'Vice President');
if (! $uber) die("Permission denied");
$email = mysql_real_escape_string($_POST['email']);
if (! mysql_query("delete from `member` where `email` = '$email'")) die("Failed to delete $email");
echo "OK";
?>
