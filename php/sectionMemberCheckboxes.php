<?
	require_once('./functions.php');
	$userEmail = $_COOKIE['email'];
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

	if(isset($userEmail)){
		$section = $_POST['section'];
		$sql = "select email,firstName,lastName from member where section='$section' and confirmed=1 order by lastName asc";
		$result = mysql_query($sql);
		while($arr = mysql_fetch_array($result)) {
			$email = $arr['email'];
			$firstName = $arr['firstName'];
			$lastName = $arr['lastName'];
			echo "<br><input type=\"checkbox\" value=\"$email\" class=\"memberCheckboxes\"> $firstName $lastName";	
		}
	}
?>