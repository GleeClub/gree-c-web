<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
mysql_set_charset("utf8");
$id = mysql_real_escape_string($_POST['id']);
if (! isset($_COOKIE['email'])) die("You must be logged in to view repertoire.");
$query = "select * from `song` where `id` = '$id'";
$result = mysql_fetch_array(mysql_query($query));
$title = $result['title'];
$desc = $result['info'];
$key = $result['key'];
$pitch = $result['pitch'];
$current = $result['current'];
$query = "select `id`, `type`, `name`, `target` from `songLink` where `song` = '$id'";
$sql = mysql_query($query);
while ($result = mysql_fetch_array($sql))
{
	$linkIds[] = $result[0];
	$linkTypes[] = $result[1];
	$linkNames[] = $result[2];
	$linkTargets[] = $result[3];
}
$query = "select `typeid`, `name`, `storage` from `mediaType` order by `order` asc";
$sql = mysql_query($query);
while ($result = mysql_fetch_array($sql))
{
	$typeids[] = $result['typeid'];
	$typenames[] = $result['name'];
	$storage[] = $result['storage'];
}
$keyvals='?,A♭,A,A♯,B♭,B,C,C♯,D♭,D,D♯,E♭,E,F,F♯,G♭,G,G♯';
echo "<h2><span id=song_title>$title</span> <span id=repertoire_header style='font-size: 10pt;' data-current='$current'></span></h2><div id=song_desc><pre>$desc</pre></div><br>";
echo "Key: <span id='song_key' data-vals='$keyvals'>$key</span><br>";
echo "Starting pitch: <span id='song_pitch' data-vals='$keyvals'>$pitch</span><br><br>";
$k = 0;
for ($j = 0; $j < count($typeids); $j++)
{
	// TODO Hide empty sections
	echo "<div id=\"block_$typeids[$j]\" data-storage=\"$storage[$j]\" data-typeid=\"$typeids[$j]\" style=\"margin-top: 16px;\"><h4>$typenames[$j]<span id=\"actions_$typeids[$j]\" class=\"rep_actions\"></span></h4>";
	for ($i = 0; $i < count($linkNames); $i++) if ($linkTypes[$i] == $typeids[$j])
	{
		$prefix = '';
		if ($storage[$j] == 'local') $prefix = "$musicdir/";
		else if ($typeids[$j] == 'video') $prefix = "http://www.youtube.com/watch?v=";
		echo "<div id=\"file_$linkIds[$i]\"><span class=link_actions></span> <span class=\"link_main\">";
		if ($linkNames[$i] == '') echo "<a href=\"about:blank\" style=\"color: red;\">NO NAME</a>";
		else if ($linkTargets[$i] == '') echo "<a href=\"about:blank\" style=\"color: red;\">$linkNames[$i]</a>";
		else echo "<a href=\"$prefix$linkTargets[$i]\" target=\"_blank\">$linkNames[$i]</a>";
		echo "</span></div>";
	}
	echo "</div>";
}
?>
