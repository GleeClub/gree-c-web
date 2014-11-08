<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase") or die("cannot select DB");
$linkid = mysql_real_escape_string($_POST['id']);
$request = mysql_real_escape_string($_POST['item']);
$query = "select `type`, `name`, `target` from `songLink` where `id` = '$linkid'";
$results = mysql_fetch_array(mysql_query($query));
if ($request == "type") echo $results[0];
else if ($request == "name") echo $results[1];
else if ($request == "target") echo $results[2];
?>