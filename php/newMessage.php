<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
if(!isset($_COOKIE['email'])){
	loginBlock();
	return;
}
?>
<div class='btn' id='backToInboxButton'><i class='icon-arrow-left'></i> Inbox</div>
<form method="post" id="newMessageForm" action="php/createMessage.php">
To: <br /><input type="text" id="members" name="members" /> <br />
Title: <br /><input id="title" type="text" name="title" /> <br />
Message: <br /><textarea id="message" rows="5" type="text" name="message" wrap="physical" maxlength="1024"></textarea><br />
<div class="btn btn-primary" onclick='return checkForm();'>Send</div>
</form>

<script type="text/javascript">
	$("#newMessageForm").submit(function(event) {
		event.preventDefault();
		$.post("php/createMessage.php", $("#newMessageForm").serialize(), function(data) {loadMessage(parseInt(data, 10));});
		return false;
	});
</script>