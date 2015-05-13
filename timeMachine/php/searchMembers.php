<?php
//This returns the results for the To: field in creating a message.
require_once('functions.php');
$q = mysql_real_escape_string($_GET['q']);

$sql = "select email, prefName, lastName from member where prefName like '$q%' or lastName like '$q%';";
$res = mysql_query($sql);
$ret = "[\n";
$i = 1;
while($arr = mysql_fetch_array($res)) {
	$pn = $arr['prefName'];
	$ln = $arr['lastName'];
	$tmp['id'] = $arr['email'];
	$tmp['name'] = $pn . " " . $ln;
	$ret2[] = $tmp;
	//$ret .= "\t{\"id\":\"$i\",\"name\":\"$pn $ln\"},\n";
	$i++;
}
$ret .= "]";
echo json_encode($ret2);
?>
