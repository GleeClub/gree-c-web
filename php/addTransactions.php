<?php
require_once('./functions.php');
$userEmail = getuser();

function encrypt($string, $key = '')
{
	if ($key == '') return base64_encode($string);
	$result = '';
	for($i=0; $i<strlen($string); $i++)
	{
		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key))-1, 1);
		$char = chr(ord($char)+ord($keychar));
		$result .= $char;
	}
	return base64_encode($result);
}

$treasurerEmail = emailFromPosition("Treasurer");
if(isset($_POST['emails']))
{
	$emailArr = json_decode($_POST['emails']);
	$amountArr = json_decode($_POST['amounts']);
	$descriptionArr = json_decode($_POST['descriptions']);
	$sendArr = json_decode($_POST['sendEmails']);
	$typeArr = json_decode($_POST['types']);
	$semArr = json_decode($_POST['semesters']);

	$count=0;
	foreach($emailArr as $email)
	{
		if ($email == '') continue; // Ignore transactions with nobody
		$sql = "insert into transaction (memberID, amount, description, semester, type) values ('".mysql_escape_string($email)."','".mysql_escape_string($amountArr[$count])."','".mysql_escape_string($descriptionArr[$count])."', '".mysql_escape_string($semArr[$count])."', '".mysql_escape_string($typeArr[$count])."')";
		mysql_query($sql);
		if($sendArr[$count]) {
			$name = fullNameFromEmail(mysql_real_escape_string($email));
			$msg = "Keep this receipt for your records.";
			$msg .= "<br />Name: " . $name;
			$msg .= "<br />Semester:  " . $semArr[$count];
			$msg .= "<br />Category:  " . $typeArt[$count];
			$msg .= "<br />Amount: " . $amountArr[$count];
			$msg .= "<br />Description: " . $descriptionArr[$count];
			$msg .= "<br />Time: " . date('l jS \of F Y');
			$msg .= "<br />Hash (for Treasurer's use): " . encrypt($d);
			$title = "Glee Club Receipt";

			$headers  = 'MIME-Version: 1.0' . "\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
			mail($treasurerEmail . ', ' . $email, $title, $msg, $headers);
		}
		$count++;
	}
	echo "OK";
}
else echo "ERR";
?>
