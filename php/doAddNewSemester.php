<?php
require_once('functions.php');

if (! $USER || ! hasPermission("edit-semester")) die("DENIED");
$name = mysql_real_escape_string($_POST['name']);
$sDD = $_POST['sDD'];
$sMM = $_POST['sMM'];
$sYYYY = $_POST['sYYYY'];
$eDD = $_POST['eDD'];
$eMM = $_POST['eMM'];
$eYYYY = $_POST['eYYYY'];

$start = "$sYYYY-$sMM-$sDD 00:00:00";
$end = "$eYYYY-$eMM-$eDD 00:00:00";

$sql = "insert into semester (semester,beginning,end) values ('$name','$start','$end')";
if(mysql_query($sql)) echo "<br><h3>Insert Results</h3><br>$name was added to the database.<br>";
else echo "<br><h3>Insert Results</h3><br>Something went wrong.<br>";
?>
