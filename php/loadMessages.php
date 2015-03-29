<?php
//Load the inbox of the user
require_once('functions.php');
$userEmail = getuser();
if(!getuser()){
	loginBlock();
	return;
}

//most recent message time for inbox
function mostRecentMessageTime($id){
	$sql = "SELECT timestamp FROM `convoMessages` where id=$id order by timestamp desc";
	$first = mysql_fetch_array(mysql_query($sql));
	return $first['timestamp'];
	//return "time";
}

$email = mysql_real_escape_string(getuser());
//Get messages in 
$res = getInbox($email);
//echo "<h2>Inbox</h2><br />";
echo "<h1 class='pull-left' style='text-decoration:underline;width:67%;text-align:center;'>Inbox</h1><div class=\"btn pull-right\" onclick='window.location.hash=\"newMessage\"'><i class=\"icon-pencil\"></i></div>";
echo "<table style='margin-left:1em;' class='table highlight' id='messagesListTable'>";
while($arr = mysql_fetch_array($res)) {
	$title = $arr['title'];
	$new = $arr['newMessages'];
	$id = $arr['id'];
	$time = date('M j g:i:sa', strtotime(mostRecentMessageTime($id)));
	//Make the string bold and show the number of new messages if any exists.
	$newstr = $new == 0 ? $title : "<b>$title <span class='badge badge-info'>$new</span></b>";
	//Get the convo members
	$member = getConvoMembers($id, $email);
	//$members = "You";
	while($arr = mysql_fetch_array($member)) {
		$members .= $arr['prefName'].' '.substr($arr['lastName'], 0, 1)."., ";
	}
	$members = substr($members, 0, -2);
	echo "<tr>
			<td style='vertical-align:middle;'><small><span class='pull-left'>$time</span></small></td>
			<td>
				<dl class='dl-horizontal'>
					<dt>$members: </dt>
					<dd><a href='#message?id=$id'> $newstr</a></dd>
				</dl>
			</td>
		</tr>";
	$members = '';
}
echo "</table>";

//don't do this yet
/*
$sql = 'SELECT * FROM `message` WHERE sender="'.$userEmail.'" OR recipient="'.$userEmail.'";';
$results = mysql_query($sql);
while($row = mysql_fetch_array($results)){
	
}
*/
/*


$html = '<table class="table" id="messagesListTable">';
$sql = 'SELECT * FROM `member` WHERE email<>"'.$userEmail.'" ORDER BY `lastName` ASC;';
$results = mysql_query($sql);
while($row = mysql_fetch_array($results)){

	//make a list of everyone and the most recent message, ordered by time and then alphabet?
	$sql2 = "SELECT * FROM `message` WHERE (sender='".$userEmail."' AND recipient='".$row['email']."') OR (sender='".$row['email']."' AND recipient='".$userEmail."') ORDER BY `timeSent` DESC LIMIT 1;";
	$results2 = mysql_fetch_array(mysql_query($sql2));
	$mostRecentMessage = '';
	if($results2['sender'] == $userEmail){
		$mostRecentMessage = '<i class="icon-share-alt"></i> ';
	}
	$mostRecentMessage = $mostRecentMessage.$results2['contents'];

	$html = $html.'<tr id="'.$row['email'].'"><td><p class="messagesListName">'.$row['firstName'].' '.$row['lastName'].'</p>
		<p class="messagesListMessage">'.$mostRecentMessage.'</p>
	</td></tr>';
}
$html = $html.'</table>';
echo $html;*/
?>
