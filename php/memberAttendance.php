<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];

$style = '<style>td { padding: 0px 10px; }</style>';
if (! isOfficer($userEmail)) die("DENIED");
echo "<html><head><meta charset='UTF-8'><title>Attendance Record</title></head><body>$style";
echo attendance($_GET['id'], 1, $CUR_SEM, "print");
echo "</body></html>";
?>
