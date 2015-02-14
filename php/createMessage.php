<?php
require_once('functions.php');
global $CUR_SEM;
$email = $_COOKIE['email'];
$members = explode(",", $_POST['members']);
$members[] = $_COOKIE['email'];
$title = mysql_real_escape_string($_POST['title']);
$msg = mysql_real_escape_string($_POST['message']);

$sql = "insert into convoMaster (title, modified) values ('$title', now())";
mysql_query($sql);
$id = mysql_insert_id(); //Get the id generated from the previous query.

$email = mysql_real_escape_string($_COOKIE['email']);
$sql = "insert into convoMessages (id, message, sender) values ('$id', '$msg', '$email')";
mysql_query($sql);

//Deal with sending a message to an entire section
foreach($members as $member) {
	if($member == "tenor1s") $section = 4;
	else if($member == "tenor2s") $section = 3;
	else if($member == "baritones") $section = 2;
	else if($member == "basses") $section = 1;
	$sql = "select `member`.`email` from `member`, `activeSemester` where `member`.`section` = '$section' and `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$CUR_SEM'";
	$res = mysql_query($sql);
	while($array = mysql_fetch_array($res)) $members[] = $array['email'];
}

//Remove duplicates
$members = array_unique($members);
foreach($members as $member) {
	$member = mysql_real_escape_string($member);
	$sql = "insert into convoMembers (id, email, newMessages) values ('$id', '$member', '1')";
	mysql_query($sql);

	//Don't send to the creator of the message
	if($member != $email) {
		//$subjectField = getConvoTitle($id);
		//sendMessageEmail($member, $email, $_POST['message'], $subjectField);
	}
}
if(($key = array_search($email, $members)) !== false) {
    unset($members[$key]);
}
$membersStr = implode(", ", $members);
sendMessageEmail($membersStr, $email, $_POST['message'], getConvoTitle($id));
echo $id;
?>
<!--
<script type="text/javascript">
window.location.hash="message?msgID=<?php echo $id?>";
</script> -->
