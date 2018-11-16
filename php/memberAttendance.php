<?php
require_once('functions.php');

$style = '<style>td { padding: 0px 10px; }</style>';
if (! hasPermission("view-attendance")) die("DENIED");
echo "<html><head><meta charset='UTF-8'><title>Attendance Record</title></head><body>$style";
echo attendanceTable($_GET['id'], true, $SEMESTER, "print");
echo "</body></html>";
?>
