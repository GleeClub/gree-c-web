<?php

require_once('/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs/db_connect.php');

$docroot = "/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs";
$musicdir = "/music";
$domain = "gleeclub.gatech.edu";
$BASEURL = "http://$domain/buzz";

// Connect to the database
$sql = "select * from variables";
$variables = mysql_fetch_array(mysql_query($sql));

//check the current semester
$CUR_SEM = $variables['semester'];

?>
