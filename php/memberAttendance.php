<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

$style = '<style>td { padding: 0px 10px; }</style>';
if (! isOfficer($userEmail)) die("DENIED");
echo "<html><head><meta charset='UTF-8'><title>Attendance Record</title></head><body>$style";
echo attendance($_GET['id'], 1, "print");
echo "</body></html>";
?>
