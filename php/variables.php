<?php

require_once('/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs/db_connect.php');

$docroot = "/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs";
$musicdir = "/music";
$BASEURL = "http://gleeclub.gatech.edu/buzz";

// Connect to the database
$sql = "select * from variables";
$variables = mysql_fetch_array(mysql_query($sql));

$CUR_SEM = $variables['semester'];
$DUES = $variables['duesAmount'];
$LATEFEE = $variables['lateFee'];
$DEPOSIT = $variables['tieDeposit'];
$GIG_REQ = $variables['gigRequirement'];
?>
