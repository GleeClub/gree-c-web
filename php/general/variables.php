<?php

require_once('/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs/db_connect.php');

$docroot = "/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs";
$musicdir = "/music";
$domain = "gleeclub.gatech.edu";
$BASEURL = "http://$domain/buzz";
$admin_email = "Glee Club Officers <gleeclub_officers@lists.gatech.edu>";

// Connect to the database
$variables = mysql_fetch_array(mysql_query("select * from variables"));
$CUR_SEM = $variables['semester'];
?>
