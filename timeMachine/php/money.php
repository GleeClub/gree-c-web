<?php session_start();?>

<head>
	<style>
		#title{
			font-size:30px;
			text-align: center;
		}
		#form{
			table-layout: fixed;
			word-wrap: break-word;
		}
		.cellwrap{
			width: 17%;
		}
		.center{
			width: 17%;
			text-align: center;
		}
		#headings{
			font-weight: bold;
			text-align: center;	
		}
		tr.topborder td {
			border-top: 1pt solid black;
		}
	</style>
	<script type="text/javascript" src="./js/drewJS.js"></script>
</head>

<?php
	if(isset($_COOKIE['email'])){
		require_once('./functions.php');
		$userEmail = $_COOKIE['email'];
		mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
		mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
	}

	//if the person is logged in
	if($userEmail!=null){
		//check the user's position in the club
		$result= mysql_fetch_array(mysql_query("select * from member where email='$userEmail'"));
		$position = $result['position'];
		
		$html ="<html>
		<div class='span11 block'>
		<p id='title'>Moneys!!</p> 
		<p id='moneyList'><table id='moneyForm'>
		<tr id='headings'>
			<td class='cellwrap'>First Name</td>
			<td class='cellwrap'>Last Name</td>
			<td  class='cellwrap'>Time of Transaction</td>
			<td  class='cellwrap'>Amount</td>
			<td  class='cellwrap'>Description</td>
			<td  class='cellwrap'>BALANCE</td>
		</tr>";
		//if they are the treasurer or the president, show them everything
		if($position=="President" || $position=="Treasurer"){
			$sql = "select * from member order by lastName asc, firstName asc";
			$result = mysql_query($sql);
			while($members=mysql_fetch_array($result)){
				$memberID = $members["email"];
				$html.="
				".getMemberMoneyRows($memberID);
			}
			$html.="
				<tr><td id='addButton'><button type='button' class='btn' onclick='addMoneyForm()'>Add Transaction</button></td></tr>
				<tr><td id='deleteButton'><button type='button' class='btn' onclick='removeMoneyForm()'>Delete Transaction</button></td></tr>
			</table></p>
			</div>
			</html>";
		}
		//if the person is not the treasurer or president, only show them their own info
		else{
			$memberID = $result["email"];
			$html.="
			".getMemberMoneyRows($memberID);
			$html.="
			</table></p>
			</div>
			</html>";
		}
	}
	//if the person isn't logged in
	else{
		$html = "<html><p id='title'><°o°> You're not logged in <°o°></p></html>";
	}

	echo $html;
?>

<?php
/**
* Returns all of the info about the person whose email matches $memberID in the form of rows (already wrapped in <tr> tags). 
* Just plug the return string between <table> tags, and you're good to go.
**/
function getMemberMoneyRows($memberID){
	$sql = "select * from member where email='$memberID'";
	$member = mysql_fetch_array(mysql_query($sql));
	$firstName = $member['firstName'];
	$lastName = $member['lastName'];

	$sql = "select * from moneyvalue where memberID='$memberID' order by time desc";	
	$moneyvalues = mysql_query($sql);

	$html = "";
	$count = 0;
	while($moneyvalue=mysql_fetch_array($moneyvalues)){
		$time = $moneyvalue['time'];
		$amount = $moneyvalue['amount'];
		$description = $moneyvalue['description'];
		$moneyvalueID = $moneyvalue['moneyvalueID'];
		$time = strftime("%b %d, %Y", strtotime($time));

		//if this is the first row for this person, then add a border to the top of the row
		if($count==0){
			$html.="<tr class='topborder' id='".$moneyvalueID."'>
			<td id='".$moneyvalueID."_fname'>$firstName</td>
			<td id='".$moneyvalueID."_lname'>$lastName</td>";
		}
		else{
			$html.="<tr id='".$moneyvalueID."'>
			<td></td>
			<td></td>";
		}

		$html.="
			<td id='".$moneyvalueID."_time'>$time</td>";

		//make the amount number red if it is negative
		if($amount>=0)		
			$html.="
			<td id='".$moneyvalueID."_amount' class='center'>$amount</td>";
		else
			$html.="
			<td id='".$moneyvalueID."_amount' class='center'><font color='red'>$amount</font></td>";

		$html.="
			<td id='".$moneyvalueID."_description'>$description</td>";

		//if this is the first row for the person, add the total babalace to the end of the row
		if($count==0){
			$sql = "select sum(amount) as balance from moneyvalue where memberID='$memberID'";
			$balanceArr =  mysql_fetch_array(mysql_query($sql));
			$balance = $balanceArr['balance'];
			if($balance>=0)		
				$html.="
					<td id='".$moneyvalueID."_balance' class='center'><font weight='bold'>$balance</font></td>";
			else
				$html.="
				<td id='".$moneyvalueID."_balance' class='center'><font color='red' weight='bold'>$balance</font></td>";
		}
		//if this is not the first row for this person, do not add anything under "Balance"
		else
			$html.="
				<td></td>";
		$html.="
		</tr>";

		$count++;
	}
	return $html;
}

?>

