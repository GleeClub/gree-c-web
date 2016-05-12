<?php
/**** Message functions ****/

function sendMessageEmail($to, $from, $message, $subjectField = "New Absence Request"){
	$fromEmail = $from;
	$from = prefNameFromEmail($from)." ".lastNameFromEmail($from);
	if(strpos($to, ',')){//being sent to multiple people
		$toField = $to;
	}
	else{//being sent to one person
		$toField = prefNameFromEmail($to)." ".lastNameFromEmail($to)."<".$to.">"; //make it format: Chris Ernst <cernst3@gatech.edu>
	}
	if(!$subjectField){$subjectField = 'Message from '.$from.'!';} //if there's no subject, it's a message...maybe.
	$messageField = '
	<html>
	<head>
		<style>
			.container{
				font-family:"lucida grande",tahoma,verdana,arial,sans-serif;
				height:100%;
				padding:5px;
				margin-bottom:3px;
			}
			body{
				background:rgb(179,179,179);
			}
			h1{
				width:100%;
				margin:auto;
				text-align:center;
				text-decoration:underline;
				margin-bottom:3px;
			}
			.from{
				width:100%;
				margin:auto;
				text-align:center;
			}
			.message{
				border-radius:2px;
				background-color:rgba(255,255,255, .7);
				padding:5px;
				border:1px solid rgba(0, 0, 0, 0.1);
				box-shadow:0 4px 7px rgba(0,0,0, .4);
				margin:5px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<p class="message">'.$message.'</p>
		</div>
	</body>
	</html>
	';
	//reply-to isn't working, but that seems to be alright because the form field is working.
	$headers = 'Content-type:text/html;\n
				Reply-To: '.$from.' <'.$fromEmail.'>' . "\n" .
				'From: '.$from.' <'.$fromEmail.'>' . "\n" .
				'X-Mailer: PHP/' . phpversion();
	mail($toField, $subjectField, $messageField, $headers);
	//echo $toField." ".$subjectField." ".$messageField." ".$headers;
}

function getNumUnreadMessages($email) {
	$sql = "select sum(newMessages) from convoMembers where email='$email'";
	$res = mysql_fetch_array(mysql_query($sql));
	$newMessages = $res['sum(newMessages)'];
	return $newMessages;
}

function getInbox($email) {
	$sql = "select * from convoMaster left join convoMembers on convoMaster.id=convoMembers.id where convoMembers.email='$email' order by convoMaster.modified desc";
	return mysql_query($sql);
}

function getConvoTitle($id) {
	$sql = "select title from convoMaster where id=$id";
	$arr = mysql_fetch_array(mysql_query($sql));
	return $arr['title'];
}

function getConvoMembers($id, $email) {
	global $USER;
	$sql = "select distinct member.prefName, member.lastName from convoMembers left join member on member.email=convoMembers.email where convoMembers.id='$id' and convoMembers.email<>'$USER'";
	return mysql_query($sql);
}

function getConvoMessages($id) {
	$sql = "select message, timestamp, member.prefName, member.lastName from convoMessages left join member on member.email=convoMessages.sender where id='$id' order by timestamp asc";
	return mysql_query($sql);	
}

function todoBlock($userEmail, $form, $list)
{
	$html = '';
	if ($form)
	{
		if(isOfficer($userEmail))
		{
			$html .= "<p>
				Names: <input id='multiTodo'>
				Todo: <br /><input id='todoText'>
				<br /><button class='btn' id='multiTodoButton'>Add Todo</button>
			</p>";

		}
		else
		{
			$html .= "<p>
				<input id='newTodo'>
				<button class='btn' id='newTodoButton'>Add Todo</button>
			</p>";
		}
	}
	if ($list)
	{
		$html .= "<div id='todos'>";
		//$sql = "SELECT * FROM `todoMembers` where memberID='$userEmail' ORDER BY todoID ASC;";
		$sql = "select todo.id, todo.text from `todo`, `todoMembers` where todo.id = todoMembers.todoID and todo.completed = '0' and todoMembers.memberID = '$userEmail' order by todo.id asc";
		$todos = mysql_query($sql);
		while ($row = mysql_fetch_array($todos, MYSQL_ASSOC)){
			$id = $row['id']; //$row['todoID'];
			$text = $row['text']; //$text['text'];
			$html .= "<div class='block'><label class='checkbox'><input type='checkbox' id='$id'> $text</label></div>";
		}
		$html .= "</div>";
	}
	return $html;
}
?>
