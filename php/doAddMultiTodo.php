<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$msg = mysql_real_escape_string($_POST['message']);
if(isset($_POST['userList'])) {
	echo $_POST['userList'];
	$userlist = $_POST['userList'];
} else {
	$userlist = mysql_real_escape_string($_COOKIE['email']);
}

if (! isset($_COOKIE['email']))
{
	echo "UNAUTHORIZED";
	exit(1);
}
$users = explode(',', $userlist);
$query = "insert into `todo` (text, completed) values(\"$msg\", 0)";
if (mysql_query($query)) echo "OK";
else exit(1);
//max(id) should be different --TH
/*$query = "select max(id) where `text` = \"$msg\" and `completed` = 0";
$results = mysql_query($query);
if (mysql_query($query)) echo "OK";
else exit(1);
$res_arr = mysql_fetch_array($results);
$id = $res_arr['id'];
*/
$id = mysql_insert_id();
if (! isOfficer($_COOKIE['email'])) // TODO handle duplicate users in the list
{
	foreach ($users as $user)
	{
		
	}
}
foreach ($users as $user)
{
	$query = "insert into `todoMembers` (memberID, todoID) values(\"$user\", \"$id\")";
	echo $user;
	echo $id;
	echo $query;
	if (mysql_query($query)) echo "OK";
	else exit(1);
}
?>