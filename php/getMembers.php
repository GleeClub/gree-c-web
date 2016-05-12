<?php
	require_once('./functions.php');
	if (! $USER) die();
	$nameType = 'prefName';
	if(isset($_POST['nameType'])) $nameType = $_POST['nameType'];
	$order = 'lastName';
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
