<?php

$SQLcurrentDatabase = 'test';
$SQLusername = 'chris';
$SQLpassword = 'testing';
$SQLhost="mysql.localhost"; // Host name

//get variables stored in the database (stuff that changes, like the current semester)
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$sql = "select * from variables where 1";
$variables = mysql_fetch_array(mysql_query($sql));

//check the current semester
$CUR_SEM = $variables['semester'];

?>