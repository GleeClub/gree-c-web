<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase") or die("cannot select DB");
$request = mysql_real_escape_string($_POST['request']);
if ($request != "typeid" && $request != "name" && $request != "storage") exit(1);
$query = "select `$request` from `mediaType` order by `order` asc";
$sql = mysql_query($query);
$i = 0;
while ($results = mysql_fetch_array($sql))
{
	if ($i++ != 0) echo "\n";
	echo "$results[0]";
}
?>
