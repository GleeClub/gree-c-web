<?php
require_once('functions.php');

if (! isset($_COOKIE['email']) || ! isOfficer($_COOKIE['email'])) die("DENIED");
$name = mysql_real_escape_string($_POST['name']);
$sql = "DELETE FROM `validSemester` WHERE `semester`='$name' LIMIT 1";
if(mysql_query($sql)) echo "<br><h3>Removal Results</h3><br>$name was removed from the database.<br>";
else echo "<br><h3>Removal Results</h3><br>Something went wrong.<br>";
?>
