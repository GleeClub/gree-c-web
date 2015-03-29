<?php
//Load a single message given a message id
require_once('functions.php');
$userEmail = getuser();
if(!getuser()){
	loginBlock();
	return;
}

$id = mysql_real_escape_string($_GET['id']);
$email = mysql_real_escape_string($userEmail);

//Clear new messages for this convo.
$sql = "update convoMembers set newMessages = 0 where id='$id' and email='$email'";
mysql_query($sql);


//Get the title for the convo
$title = getConvoTitle($id);

//Get the convo members
$res = getConvoMembers($id, $email);
$members = "You";
while($arr = mysql_fetch_array($res)) {
	$members .= ", " . $arr['prefName'];
}

//Get the convo messages
$res = getConvoMessages($id);
echo "<div class='btn' id='backToInboxButton'><i class='icon-arrow-left'></i> Inbox</div>";
echo "<div class='page-header'><h1>$title";
echo "<small> – Between $members</small></h1></div>";
echo "<table class='table other-every-other no-highlight'>";
while($arr = mysql_fetch_array($res)) {
	$pn = $arr['prefName'].' '.substr($arr['lastName'], 0, 1).'.';
	$msg = $arr['message'];
	$time = date('M j g:i:sa', strtotime($arr['timestamp']));
	echo "<tr><td style='vertical-align:middle;'><small>$time</small></td><td>";
	//echo "<td>$pn:</td>";
	//echo "<td>$msg</td>";
	echo "<dl class='dl-horizontal'>
		<dt>$pn:</dt>
		<dd>$msg</dd>
		</dl>
	";
	echo "</td></tr>";
}
echo '
	<tr><td></td>
		<td>
			<dl class="dl-horizontal">
				<dt>You:</dt>
				<dd>
					<form id="messageSubmit" method="post" class="form-inline"> 
					<input type="hidden" name="msgID" value="'.$id.'" />
					<input type="text" name="message" wrap="physical" maxlength="1024" style="width:98%;">
					<div class="btn btn-primary" id="messageFormSubmit" onclick="$(\'#messageSubmit\').submit()">Reply</div>
					</form>
				<dd>
			</dl>
		</td>
	</tr>
	</table>';


?>
<script type="text/javascript">
	$("#messageSubmit").submit(function(event) {
		event.preventDefault();
		$.post("php/doReply.php", $("#messageSubmit").serialize(), function(data) {loadMessage(<?php echo $id ?>);});
		return false;
	});
</script>