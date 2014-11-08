<?php
	require_once('./functions.php');
	$userEmail = $_COOKIE['email'];
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

	$treasurerEmail = getTreasurerEmail();
	if(isset($_POST['emails'])){
		$emails = $_POST['emails'];
		$emailArr = json_decode($emails);

		$amounts = $_POST['amounts'];
		$amountArr = json_decode($amounts);

		$descriptions = $_POST['descriptions'];
		$descriptionArr = json_decode($descriptions);

		$sendEmails = $_POST['sendEmails'];
		$sendArr = json_decode($sendEmails);

		$count=0;
		foreach($emailArr as $email){
			$sql = "insert into moneyvalue (memberID,amount,description) values ('".mysql_escape_string($email)."','".mysql_escape_string($amountArr[$count])."','".mysql_escape_string($descriptionArr[$count])."')";
			mysql_query($sql);
			if($sendArr[$count]) {
				$name = fullNameFromEmail(mysql_real_escape_string($email));
				$msg = "Keep this receipt for your records.";
				$msg .= "<br />Name: " . $name;
				$msg .= "<br />Description: " . $descriptionArr[$count];
				$msg .= "<br />Amount: " . $amountArr[$count];
				$d = date('l jS \of F Y');
				$msg .= "<br />Time: $d";
				$msg .= "<br />Hash (for Treasurer's use): " . encrypt($d);
				$title = "Glee Club Receipt";

				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				mail($treasurerEmail . ', ' . $email, $title, $msg, $headers);
			}
			$count++;

		}
	}
	else
		echo "<html>It didn't work ;(</html>";
?>