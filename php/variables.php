<?php

require_once('/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs/db_vars.php');

$docroot = "/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs";
$musicdir = "/music";
$BASEURL = 'http://mensgleeclub.gatech.edu/buzz';

//get variables stored in the database (stuff that changes, like the current semester)
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase") or die("cannot select DB");
$sql = "select * from variables";
$variables = mysql_fetch_array(mysql_query($sql));

//check the current semester
$CUR_SEM = $variables['semester'];
$DUES = $variables['duesAmount'];
$LATEFEE = $variables['lateFee'];
$DEPOSIT = $variables['tieDeposit'];
$GIG_REQ = $variables['gigRequirement'];
?>
