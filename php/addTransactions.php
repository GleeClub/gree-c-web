<?php
require_once('./functions.php');

/*function encrypt($string, $key = '')
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
}*/

$treasurerEmail = getPosition("Treasurer")[0];
if (! $CHOIR) die("Choir is not set");
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
		if (! mysql_query("insert into transaction (memberID, choir, amount, description, semester, type) values ('".mysql_escape_string($email)."', '$CHOIR', '".mysql_escape_string($amountArr[$count])."','".mysql_escape_string($descriptionArr[$count])."', '".mysql_escape_string($semArr[$count])."', '".mysql_escape_string($typeArr[$count])."')")) die(mysql_error());
		if($sendArr[$count])
		{
			$name = fullNameFromEmail(mysql_real_escape_string($email));
			$msg = "Keep this receipt for your records.";
			$msg .= "<br />Name: " . $name;
			$msg .= "<br />Semester:  " . $semArr[$count];
			$result = mysql_fetch_array(mysql_query("select `name` from `transacType` where `id` = '" . $typeArr[$count] . "'"));
			$msg .= "<br />Category:  " . $result['name'];
			$msg .= "<br />Amount: " . $amountArr[$count];
			$msg .= "<br />Description: " . $descriptionArr[$count];
			$msg .= "<br />Date: " . date('l jS \of F Y');
			//$msg .= "<br />Hash (for Treasurer's use): " . encrypt($d);
			$title = choirname($CHOIR) . " Receipt";

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
