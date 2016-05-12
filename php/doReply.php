<?php 
require_once('functions.php');

$email = $USER;
$id = mysql_real_escape_string($_POST['msgID']);
$msg = mysql_real_escape_string($_POST['message']);
$sql = "insert into convoMessages (id, message, sender) values ('$id', '$msg', '$email');";
mysql_query($sql);

$sql = "update convoMembers set newMessages = newMessages + 1 where id='$id'";
mysql_query($sql);

$sql = "update convoMaster set modified=now() where id='$id'";
mysql_query($sql);

$sql = "select email from convoMembers where id='$id'";
$res = mysql_query($sql);
$members = array();
while($arr = mysql_fetch_array($res)) {
	$e = $arr['email'];

	if(mysql_real_escape_string($e) != $email) {
		//sendMessageEmail($e, $id);
		//$subjectField = 'Re: '.getConvoTitle($id);
		//sendMessageEmail($e, $email, $_POST['message'], $subjectField);
		$members[] = $e;
	}
}
$membersStr = implode(", ", $members);
sendMessageEmail($membersStr, $email, $_POST['message'], 'Re: '.getConvoTitle($id));

?>
