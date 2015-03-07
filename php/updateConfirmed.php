<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];
if (! isOfficer($userEmail)) die("DENIED");

$member = mysql_real_escape_string($_POST['email']);
$semester = mysql_real_escape_string($_POST['semester']);
$value = $_POST['value'];
if ($value == '1') $query = "insert into `activeSemester` (`member`, `semester`) values ('$member', '$semester')";
else if ($value == '0') $query = "delete from `activeSemester` where `member` = '$member' and `semester` = '$semester'";
else die("BAD_VALUE $value");
if (! mysql_query($query)) die("FAIL: " . mysql_error());
echo "OK";
?>
