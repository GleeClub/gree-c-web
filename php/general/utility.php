<?php
/**** Utility functions ****/

function encrypt2($string)
{
	return base64_encode($string ^ "12345678900987654321qwertyuiopasdfghjklzxcvbnm,.");
}

function decrypt2($string)
{
	return base64_decode($string) ^ "12345678900987654321qwertyuiopasdfghjklzxcvbnm,.";
}

function valid_date($string)
{
	$re_date = '/^\s*\d\d\d\d-\d\d-\d\d\s*$/';
	if (! preg_match($re_date, $string)) return false;
	$arr = preg_split('/-/', $string);
	return checkdate($arr[1], $arr[2], $arr[0]);
	return true;
}

function valid_time($string)
{
	$re_time = '/^\s*(1[012]|0?[0-9])(:[0-5][0-9])?\s*[AaPp][Mm]\s*$/';
	return preg_match($re_time, $string);
}

/**** Semester and member info functions ****/

function getCurrentSemester() {
	$sql = "SELECT semester FROM variables";
	$arr = mysql_fetch_array(mysql_query($sql));
	return $arr['semester'];
}

function getuser()
{
	global $sessionkey;
	if (! isset($_COOKIE['email'])) return false;
	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $sessionkey, base64_decode($_COOKIE['email']), MCRYPT_MODE_ECB), "\0");
}

function getchoir()
{
	global $sessionkey;
	if (! isset($_COOKIE['choir'])) return false;
	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $sessionkey, base64_decode($_COOKIE['choir']), MCRYPT_MODE_ECB), "\0");
}

function dropdown($options, $name, $selected = '', $disabled = 0)
{
	$ret = "<select name='$name' class='$name'" . ($disabled ? " disabled" : "") . ">";
	foreach ($options as $value => $option) $ret .= "<option value='$value'" . ($value == $selected ? " selected" : "") . ">$option</option>";
	$ret .= "</select>";
	return $ret;
}

function radio($options, $name, $selected = '', $disabled = 0)
{
	$ret = "";
	foreach ($options as $value => $option) $ret .= "<span class='radio-option'><input type='radio' name='$name' value='$value'" . ($selected == $value ? " checked" : "" ) . ($disabled ? " disabled" : "") . "> $option</span>";
	return $ret;
}

// First "Pref" Last if pref exists && pref != first
function completeNameFromEmail($email) {
	if ($email == '') return '';
	$sql = "SELECT firstName, lastName, prefName FROM `member` WHERE email='$email';";
	$res = mysql_fetch_array(mysql_query($sql));
	if(!empty($res['prefName']) && $res['firstName'] != $res['prefName'])
		return $res['firstName'] . ' "' . $res['prefName'] . '" ' . $res['lastName'];
	else
		return $res['firstName'] . " " . $res['lastName'];
}

function fullNameFromEmail($email) {
	return firstNameFromEmail($email) . " " . lastNameFromEmail($email);
}

function firstNameFromEmail($email){
	if ($email == '') return '';
	$sql = "SELECT firstName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	return $result["firstName"];
}

function prefNameFromEmail($email){
	if ($email == '') return '';
	$sql = "SELECT prefName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	if ($result["prefName"] == '') return firstNameFromEmail($email);
	return $result["prefName"];
}

function lastNameFromEmail($email){
	if ($email == '') return '';
	$sql = "SELECT lastName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	return $result["lastName"];
}

function prefFullNameFromEmail($email){
	return prefNameFromEmail($email).' '.lastNameFromEmail($email);
}

function positions($email)
{
	global $CUR_SEM; // TODO Semester filtering
	$choir = getchoir();
	$result = mysql_query("select `role`.`name` from `role`, `memberRole` where `memberRole`.`member` = '" . mysql_real_escape_string($email) . "' and `memberRole`.`role` = `role`.`id` and (`role`.`choir` = '$choir' or `role`.`name` = 'Webmaster') order by `role`.`rank` asc");
	if (mysql_num_rows($result) == 0) return array("Member");
	$ret = array();
	while ($row = mysql_fetch_array($result)) $ret[] = $row["name"];
	return $ret;
}

function hasPosition($email, $position)
{
	if ($position == "Member")
	{
		if (mysql_num_rows(mysql_query("select * from `member` where `email` = '" . mysql_real_escape_string($email) . "'"))) return true; # TODO Active semester
		return false;
	}
	if (array_search($position, positions($email)) !== false) return true;
	return false;
}

function getPosition($position = "Member")
{
	global $CUR_SEM; // TODO Semester filtering
	$ret = array();
	if ($position == "Member")
	{
		$result = mysql_query("select `email` from `member`");
		while ($row = mysql_fetch_array($result)) $ret[] = $row["email"];
		return $ret;
	}
	$choir = getchoir();
	$result = mysql_query("select `memberRole`.`member` as `member` from `role`, `memberRole` where `role`.`name` = '" . mysql_real_escape_string($position) . "' and `role`.`id` = `memberRole`.`role` and `role`.`choir` = '$choir'");
	while($row = mysql_fetch_array($result)) $ret[] = $row["member"];
	return $ret;
}

function profilePic($email)
{
	$default = "http://placekitten.com/g/256/256";
	if ($email == '') return $default;
	$sql = "SELECT picture FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	if ($result['picture'] == '') return $default;
	else return $result['picture'];
}

function sectionFromEmail($email, $friendly = 0, $semester = "")
{
	global $CUR_SEM;
	if ($semester == "") $semester = $CUR_SEM;
	if ($email == '') return ($friendly ? "" : 0);
	$choir = getchoir();
	$sql = mysql_query("select `sectionType`.`id`, `sectionType`.`name` from `activeSemester`, `sectionType` where `activeSemester`.`member` = '$email' and `activeSemester`.`section` = `sectionType`.`id` and `activeSemester`.`semester` = '$semester' and `activeSemester`.`choir` = '$choir'");
	if (mysql_num_rows($sql) == 0) return ($friendly ? "" : 0);
	$result = mysql_fetch_array($sql, MYSQL_ASSOC);
	return $friendly ? $result['name'] : $result['id'];
}

function enrollment($email, $semester = '')
{
	global $CUR_SEM;
	if ($semester == '') $semester = $CUR_SEM;
	$choir = getchoir();
	$query = mysql_query("select `enrollment` from `activeSemester` where `member` = '$email' and `semester` = '$semester' and `choir` = '$choir'");
	if (mysql_num_rows($query) != 1) return "inactive";
	$result = mysql_fetch_array($query);
	return $result['enrollment'];
}

function isUber($email)
{
	// Webmaster needs full access for debugging
	// And as long as I make 95% of the commits, I need access too.  -- Matthew Schauer
	if (hasPosition($email, "Instructor") || hasPosition($email, "President") || hasPosition($email, "Vice President") || hasPosition($email, "Webmaster") || $email == "awesome@gatech.edu") return true;
	return false;
}

function isOfficer($email)
{
	if (isUber($email)) return true;
	if (hasPosition($email, "President") || hasPosition($email, "Vice President") || hasPosition($email, "Treasurer") || hasPosition($email, "Manager")) return true;
	return false;
}

function canEditEvents($email)
{
	if (isUber($email)) return true;
	if (hasPosition($email, "President") || hasPosition($email, "Vice President") || hasPosition($email, "Liaison")) return true;
	return false;
}

function attendancePermission($email, $event)
{
	if (isOfficer($email)) return true;
	if (! hasPosition($email, "Section Leader")) return false;
	$result = mysql_fetch_array(mysql_query("select `section`, `type` from `event` where `eventNo` = '$event'"));
	if ($result['type'] != 'sectional') return false;
	$eventSection = $result['section'];
	if ($eventSection == 0) return true;
	if (sectionFromEmail($email) == $eventSection) return true;
	return false;
}

function getMemberAttribute($attribute, $email){
        $sql = "SELECT $attribute FROM member WHERE email='$email';";
        $result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
        return $result[$attribute];
}

function members($cond = "")
{
	global $CUR_SEM;
	$ret = array("" => "(nobody)");
	$sql = "";
	$choir = getchoir();
	if ($cond == "active") $sql = "select `member`.`firstName`, `member`.`lastName`, `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$CUR_SEM' and `activeSemester`.`choir` = '$choir' order by `member`.`lastName` asc";
	else $sql = "select `firstName`, `lastName`, `email` from `member` order by `lastName` asc";
	$results = mysql_query($sql);
	while ($row = mysql_fetch_array($results)) $ret[$row['email']] = $row['lastName'] . ", " . $row['firstName'];
	return $ret;
}

function memberDropdown($member = '')
{
	return dropdown(members(), "member", $member);
}

function semesters()
{
	$ret = array();
	$results = mysql_query("select `semester` from `semester` order by `beginning` desc");
	while ($row = mysql_fetch_array($results)) $ret[$row['semester']] = $row['semester'];
	return $ret;
}

function fee($type)
{
	$choir = getchoir();
	$query = mysql_query("select `amount` from `fee` where `choir` = '$choir' and `id` = '$type'");
	if (mysql_num_rows($query) == 0) return 0;
	$row = mysql_fetch_array($query);
	return $row["amount"];
}

function semesterDropdown()
{
	GLOBAL $CUR_SEM;
	return dropdown(semesters(), "semester", $CUR_SEM);
}

function sections()
{
	$ret = array();
	$choir = getchoir();
	$results = mysql_query("select * from `sectionType` where `choir` = '$choir' order by `id` desc");
	while ($row = mysql_fetch_array($results)) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function uniforms()
{
	$ret = array();
	$choir = getchoir();
	$result = mysql_query("select * from `uniform` where `choir` = '$choir'");
	while ($row = mysql_fetch_array($result)) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function choirs()
{
	$ret = array();
	$result = mysql_query("select * from `choir`");
	while ($row = mysql_fetch_array($result)) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function choirname($choir)
{
	if (! $choir) return "Georgia Tech Choirs";
	$row = mysql_fetch_array(mysql_query("select `name` from `choir` where `id` = '$choir'"));
	return $row["name"];
}

function eventTypes()
{
	$ret = array();
	$result = mysql_query("select * from `eventType`");
	while ($row = mysql_fetch_array($result)) $ret[$row["id"]] = $row["name"];
	return $ret;
	#if ($eventNo && $value > 2 && $row['typeNo'] <= 2) continue;
}

/**** Misc ****/

// Delete the file with a given ID from the repertoire repository
function repertoire_delfile($id)
{
	global $docroot, $musicdir;
	$query = "select `target`, `type` from `songLink` where `id` = '$id'";
	$result = mysql_fetch_array(mysql_query($query));
	$file = urldecode($result[0]);
	if ($file == '') return true;
	$type = $result[1];
	$query = "select `storage` from `mediaType` where `typeid` = '$type'";
	$result = mysql_fetch_array(mysql_query($query));
	if ($result[0] != 'local');
	if (! preg_match('/^' . $musicdir . '/', $file) || preg_match('/\/\.\./', $file)); // FIXME
	unlink($docroot . $file);
	return true;
}

function loginBlock(){
$html = '
	<div class="span3 block">
		<form class="form-inline" action="php/checkLogin.php" method="post">
		  <input type="text" class="input-medium" id="signInEmail" placeholder="gburdell3@gatech.edu" name="email" />
		  <input type="password" class="input-medium" id="signInPassword" placeholder="password" name="password" />
		  <button type="submit" value="Sign In" class="btn">Sign in</button>
		</form>
		<a href="#forgotPassword">Forgot Password?</a>
	</div>
';
echo $html;
}
?>
