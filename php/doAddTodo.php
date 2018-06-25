<?php
require_once('functions.php');
$msg = mysql_real_escape_string($_POST['message']);
if(isset($_POST['userList'])) {
	echo $_POST['userList'];
	$userlist = $_POST['userList'];
} else {
	$userlist = $USER;
}

if (! $USER)
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
if (! hasPermission("add-multi-todo")) // TODO handle duplicate users in the list
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
