<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

$sql='SELECT * FROM `chatboxMessage` ORDER BY timeSent DESC LIMIT 1;';
$results = mysql_fetch_array(mysql_query($sql));

$contents = $results['contents'];
$words = explode(' ', $contents);
foreach($words as $value){
	if((strpos($value, 'www.') === 0) || (strpos($value, 'http://') === 0) || (strpos($value, 'https://') === 0)){
		$contents = $value;
	}
}

echo $contents;
?>