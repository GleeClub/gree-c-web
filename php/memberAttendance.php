<?php
require_once('functions.php');

$style = '<style>td { padding: 0px 10px; }</style>';
if (! isOfficer($USER)) die("DENIED");
echo "<html><head><meta charset='UTF-8'><title>Attendance Record</title></head><body>$style";
echo attendance($_GET['id'], 1, $SEMESTER, "print");
echo "</body></html>";
?>
