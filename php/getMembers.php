<?php
	require_once('./functions.php');
	$userEmail = $_COOKIE['email'];
	$nameType = 'prefName';
	if(isset($_POST['nameType']))
	{
		$nameType = $_POST['nameType'];
	}
	$order = 'lastName';
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
	$sql = "select firstName,lastName,prefName,email from member order by $order";
	$result = mysql_query($sql);
	$arr = array();
	$temp = array();
	$count=0;
	while($resultArray = mysql_fetch_array($result)){
		if($nameType == 'both')
			if($resultArray['firstName'] != $resultArray['prefName'] && !empty($resultArray['prefName']))
				$temp[0] = $resultArray['firstName'] . ' "' . $resultArray['prefName'] . '"';
			else
				$temp[0] = $resultArray['firstName'];
		else
			$temp[0] = $resultArray[$nameType];
		$temp[1] = $resultArray["lastName"];
		$temp[2] = $resultArray["email"];
		$arr[$count] = json_encode($temp);
		$count++;
	}
	$jsonArr = json_encode($arr);
	echo $jsonArr;
?>
