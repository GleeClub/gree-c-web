<?php
//it would seem you cannot connect to the database from outside a function and inside a function
require_once('functions.php');
if(! $USER)
{
	loginBlock();
	exit(1);
}

function user_money_table($memberID)
{
	global $CHOIR;
	if (query("select * from `transaction` where `memberID` = ? and `choir` = ? and `resolved` = '0' order by time desc", [$memberID, $CHOIR], QCOUNT) == 0)
		return "<span style='color: gray'>(No transactions)</span><br>";
	$html = "<table style='width: 100%'>";
	{
		$time = $transaction['time'];
		$amount = $transaction['amount'];
		$description = $transaction['description'];
		$id = $transaction['transactionID'];
		$type = $transaction['type'];
		$sem = $transaction['semester'];
		$time = strftime("%b %d, %Y", strtotime($time));
		$result = query("select `name` from `transacType` where `id` = ?", [$type], QONE);
		if (! $result) die("Bad transaction type");
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
	global $SEMESTER;
	$count = attendance($userEmail, 3);
	$result = query("select `gigreq` from `semester` where `semester` = ?", [$SEMESTER], QONE);
	if (! $result) die("Invalid semester");
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
	global $SEMESTER, $CHOIR;
	$html = "";
	$dues = query("select sum(`amount`) as `balance` from `transaction` where `memberID` = ? and `type` = 'dues' and `semester` = ?", [$userEmail, $SEMESTER], QONE)["balance"];
	if ($dues == '') $dues = 0;
	$tie = query("select sum(`amount`) as `balance` from `transaction` where `memberID` = ? and `type` = 'deposit'", [$userEmail], QONE)["balance"];
	if ($tie == '') $tie = 0;
	$html .= "<table><tr><td>";
	if ($dues >= 0) $html .= "<span class='color: green'><i class='icon-ok'></i></span>";
	else $html .= "<span class='color: red'><i class='icon-remove'></i></span>";
	$html .= "</td><td>Dues</td></tr><tr><td>";
	if ($tie >= fee("tie")) $html .= "<span class='color: green'><i class='icon-ok'></i></span>";
	else $html .= "<span class='color: red'><i class='icon-remove'></i></span>";
	$html .= "</td><td>Tie Deposit</td></tr></table><br>";
	$result = query("select `tie` from `tieBorrow` where `member` = ? and `dateIn` is null", [$userEmail], QONE);
	if (! $result) $html .= "You do <b>not</b> have a tie checked out.";
	else $html .= "You have tie <b>" . $result['tie'] . "</b> checked out.";
	$html .= "<br>";
	$balance = balance($userEmail);
	$choir = choirname($CHOIR);
	if ($balance > 0) $html .= "$choir owes you <span style='font-weight: bold; color: blue'>\$$balance</span>.";
	else if ($balance < 0) { $balance *= -1; $html .= "You owe $choir <span style='font-weight: bold; color: red'>\$$balance</span>."; }
	else $html .= "Your $choir balance is <span style='font-weight: bold'>\$0</span>.";
	$html .= "<br><br>" . user_money_table($userEmail);
	return "$html";
}

function announcements($userEmail)
{
	global $CHOIR;
	$html = "<p class='lead'>Announcements <small>– Obviously each thing is the most important thing.</small></p>";
	//announcement block
	//Show only announcements less than a month old and unarchived
	foreach (query("select * from `announcement` where date_add(timePosted, interval 1 month) > now() and `choir` = ? and `archived` = 0 order by `timePosted` desc limit 0, 3", [$CHOIR], QALL) as $announcement)
	{
		$timestamp = strtotime($announcement['timePosted']);
		$dayPosted = date( 'M j, Y', $timestamp);
		$timePosted = date( 'g:i a', $timestamp);
		$op = $announcement['memberID'];
		$mid = $announcement['announcementNo'];
		$name = prefNameFromEmail($op);
		$text = nl2br(htmlspecialchars($announcement["announcement"]));
		if(hasPermission("edit-announcements")) $html .= "<div class='block' id='announce".$mid."'><p><b>$dayPosted $timePosted</b><i class='icon-remove archiveButton' onclick='archiveAnnouncement(".$mid.")' style='float: right'></i><br />$text<br /><small style='color:grey'>&mdash; $name</small></p></div>";
		else $html .= "<div class='block'><p><b>$dayPosted $timePosted</b><br />".$announcement['announcement']."<br /><small style='color:grey'>&mdash;$name</small></p></div>";
	}
	$html .= "<button type='button' id='allAnnounceButton' class='btn' href='#annoucnements'>See All Announcements</button>";
	return $html;
}

echo "<div class='block span5' id='attendanceHistory'>";
echo attendanceHistory($USER);
echo "</div><div class='span6 block' style='float:right'>";
echo gigBlock($USER);
echo info($USER);
echo "</div><div class='span6 block' style='float:right'>";
echo announcements($USER);
echo "</div><div class='span6 block' style='float:right'>";
echo todoBlock($USER, true, true);
echo "</div>";
?>
