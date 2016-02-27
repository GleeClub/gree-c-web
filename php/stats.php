<?php
//it would seem you cannot connect to the database from outside a function and inside a function
require_once('functions.php');
$userEmail = getuser();
if(!getuser())
{
	loginBlock();
	exit(1);
}

function user_money_table($memberID)
{
	$sql = "select * from transaction where memberID = '$memberID' and `resolved` = '0' order by time desc";
	$transactions = mysql_query($sql);
	if (mysql_num_rows($transactions) == 0) return "<span style='color: gray'>(No transactions)</span><br>";
	$html = "<table style='width: 100%'>";
	$count = 0;
	while($transaction = mysql_fetch_array($transactions))
	{
		$time = $transaction['time'];
		$amount = $transaction['amount'];
		$description = $transaction['description'];
		$id = $transaction['transactionID'];
		$type = $transaction['type'];
		$sem = $transaction['semester'];
		$time = strftime("%b %d, %Y", strtotime($time));
		$sql = "select `name` from `transacType` where `id` = '$type'";
		$result = mysql_fetch_array(mysql_query($sql));
		$typename = $result['name'];
		$desc = '';
		if ($type == 'dues' || $type == 'deposit')
		{
			$desc = "$sem $typename";
			if ($description != '') $desc .= " <span style='color: gray'>($description)</span>";
		}
		else
		{
			$desc = "$typename ($description)";
		}
		$html .= "<tr data-id='$id'><td>$time</td>";
		//make the amount number red if it is negative
		if($amount>=0) $html.="<td class='center'>$amount</td>";
		else $html.="<td class='center' style='color: red'>$amount</td>";
		$html.="<td>$desc</td></tr>";
		$count++;
	}
	$html .= "</table>";
	return $html;
}

function attendanceHistory($userEmail)
{
	return '<h2>Attendance History</h2><h3>Score: '. attendance($userEmail, 0) . '</h3><span style="color: gray; font-style: italic">Hover over a point change for explanation</span>' . attendance($userEmail, 2);
}

function gigBlock($userEmail)
{
	global $CUR_SEM;
	$count = attendance($userEmail, 3);
	$result = mysql_fetch_array(mysql_query("select `gigreq` from `semester` where `semester` = '$CUR_SEM'"));
	$gigreq = $result['gigreq'];
	if ($count < $gigreq) $precentProgress = floor(100 * $count / $gigreq);
	else $precentProgress = 100;

	return "<div class='btn btn-danger' id='notificationsButton'>Enable Notifications</div>
		<p>You have attended $count of $gigreq required volunteer gigs:</p>
		<div class='progress progress-striped active'>
		<div class='bar' style='width: ".$precentProgress."%;'></div>
		</div>";
}

function info($userEmail)
{
	global $CUR_SEM;
	$html = "";
	$sql = "select sum(`amount`) as `balance` from `transaction` where `memberID` = '$userEmail' and `type` = 'dues' and `semester` = '$CUR_SEM'";
	$result = mysql_fetch_array(mysql_query($sql));
	$dues = $result['balance'];
	if ($dues == '') $dues = 0;
	$sql = "select sum(`amount`) as `balance` from `transaction` where `memberID` = '$userEmail' and `type` = 'deposit'";
	$result = mysql_fetch_array(mysql_query($sql));
	$tie = $result['balance'];
	if ($tie == '') $tie = 0;
	$html .= "<table><tr><td>";
	if ($dues >= 0) $html .= "<span class='color: green'><i class='icon-ok'></i></span>";
	else $html .= "<span class='color: red'><i class='icon-remove'></i></span>";
	$html .= "</td><td>Dues</td></tr><tr><td>";
	if ($tie >= fee("tie")) $html .= "<span class='color: green'><i class='icon-ok'></i></span>";
	else $html .= "<span class='color: red'><i class='icon-remove'></i></span>";
	$html .= "</td><td>Tie Deposit</td></tr></table><br>";
	$sql = "select `tie` from `tieBorrow` where `member` = '$userEmail' and `dateIn` is null";
	$query = mysql_query($sql);
	if (mysql_num_rows($query) == 0) $html .= "You do <b>not</b> have a tie checked out.";
	else
	{
		$result = mysql_fetch_array($query);
		$html .= "You have tie <b>" . $result['tie'] . "</b> checked out.";
	}
	$html .= "<br>";
	$balance = balance($userEmail);
	if ($balance > 0) $html .= "The Glee Club owes you <span style='font-weight: bold; color: blue'>\$$balance</span>.";
	else if ($balance < 0) { $balance *= -1; $html .= "You owe the Glee Club <span style='font-weight: bold; color: red'>\$$balance</span>."; }
	else $html .= "Your Glee Club balance is <span style='font-weight: bold'>\$0</span>.";
	$html .= "<br><br>" . user_money_table($userEmail);
	return "$html";
}

function announcements($userEmail)
{
	$html = "<p class='lead'>Announcements <small>–Obviously each thing is the most important thing.</small></p>";
	//announcement block
	//Show only announcements less than a month old and unarchived
	$sql = "SELECT * FROM `announcement` WHERE date_add(timePosted, INTERVAL 1 MONTH) > now()  AND `archived`=0 ORDER BY `timePosted` DESC LIMIT 0, 3";
	$result = mysql_query($sql);
	while ($announcement=mysql_fetch_array($result))
	{
		$timestamp = strtotime($announcement['timePosted']);
		$dayPosted = date( 'M j, Y', $timestamp);
		$timePosted = date( 'g:i a', $timestamp);
		$op = $announcement['memberID'];
		$mid = $announcement['announcementNo'];
		$name = prefNameFromEmail($op);
		if(isOfficer($userEmail)) $html .= "<div class='block' id='announce".$mid."'><p><b>$dayPosted $timePosted</b><i class='icon-remove archiveButton' onclick='archiveAnnouncement(".$mid.")' style='float: right'></i><br />".$announcement['announcement']."<br /><small style='color:grey'>&mdash;$name</small></p></div>";
		else $html .= "<div class='block'><p><b>$dayPosted $timePosted</b><br />".$announcement['announcement']."<br /><small style='color:grey'>&mdash;$name</small></p></div>";
	}
	$html .= "<button type='button' id='allAnnounceButton' class='btn' href='#annoucnements'>See All Announcements</button>";
	return $html;
}

echo "<div class='block span5' id='attendanceHistory'>";
echo attendanceHistory($userEmail);
echo "</div><div class='span6 block' style='float:right'>";
echo gigBlock($userEmail);
echo info($userEmail);
echo "</div><div class='span6 block' style='float:right'>";
echo announcements($userEmail);
echo "</div><div class='span6 block' style='float:right'>";
echo todoBlock($userEmail, true, true);
echo "</div>";
?>
