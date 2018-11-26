<?php
require_once('functions.php');
if (isset($_POST['userList'])) $userlist = $_POST['userList'];
else $userlist = $USER;

if (! $USER) err("UNAUTHORIZED");
$users = explode(',', $userlist);
$id = query("insert into `todo` (text, completed) values (?, 0)", [$_POST["message"]], QID);
if (! hasPermission("add-multi-todo")) // TODO handle duplicate users in the list
{
	foreach ($users as $user)
	{
		
	}
}
foreach ($users as $user)
	query("insert into `todoMembers` (memberID, todoID) values (?, ?)", [$user, $id]);
echo "OK";
?>
