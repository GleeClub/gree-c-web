<?php
session_start();
require_once('functions.php');
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
	if($member == "tenor1s"){
		$sql = "SELECT email FROM member WHERE section='Tenor 1' AND confirmed=1";
		$res = mysql_query($sql);
		while($array = mysql_fetch_array($res)) {
			$members[] = $array['email'];
		}
	} else if($member == "tenor2s") {
		$sql = "SELECT email FROM member WHERE section='Tenor 2' AND confirmed=1";
		$res = mysql_query($sql);
		while($array = mysql_fetch_array($res)) {
			$members[] = $array['email'];
		}
	} else if($member == "baritones") {
		$sql = "SELECT email FROM member WHERE section='Baritone' AND confirmed=1";
		$res = mysql_query($sql);
		while($array = mysql_fetch_array($res)) {
			$members[] = $array['email'];
		}
	} else if($member == "basses") {
		$sql = "SELECT email FROM member WHERE section='Bass' AND confirmed=1";
		$res = mysql_query($sql);
		while($array = mysql_fetch_array($res)) {
			$members[] = $array['email'];
		}
	}
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
