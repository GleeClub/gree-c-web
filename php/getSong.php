<?php
require_once('functions.php');
if (! $USER) err("You must be logged in to view repertoire.");
$result = query("select * from `song` where `id` = ?", [$_POST["id"]], QONE);
if (! $result) err("Bad song ID");
$title = $result['title'];
$desc = $result['info'];
$key = $result['key'];
$pitch = $result['pitch'];
$current = $result['current'];
$keys = "?,A♭,a♭,A,a,a♯,B♭,b♭,B,b,C♭,C,c,C♯,c♯,D♭,D,d,d♯,E♭,e♭,E,e,F,f,F♯,f♯,G♭,G,g,g♯";
$pitches = "?,A♭,A,A♯,B♭,B,C,C♯,D♭,D,D♯,E♭,E,F,F♯,G♭,G,G♯";
echo "<h2><span id=song_title>$title</span> <span id=repertoire_header style='font-size: 10pt;' data-current='$current'></span></h2>";
if ($desc != "") echo "<div id=song_desc><pre>$desc</pre></div><br>";
echo "<b>Key:</b> <span id='song_key' data-vals='$keys'>$key</span><br>";
echo "<b>Starting pitch:</b> <span id='song_pitch' data-vals='$pitches'>$pitch</span><br><br>";
$types = query("select `typeid`, `name`, `storage` from `mediaType` order by `order` asc", [], QALL);
$k = 0;
foreach ($types as $type)
{
	$links = query("select `id`, `name`, `target` from `songLink` where `song` = ? and `type` = ?", [$_POST["id"], $type["typeid"]], QALL);
	echo "<div id='block_" . $type["typeid"] . "' data-storage='" . $type["storage"] . "' data-typeid='" . $type["typeid"] . "' style='margin-top: 16px;'><h4>" . $type["name"] . "<span id='actions_" . $type["typeid"] . "' class='rep_actions'></span></h4>";
	foreach ($links as $link)
	{
		$prefix = "";
		if ($type["storage"] == "local") $prefix = "$musicdir/";
		else if ($type["id"] == "video") $prefix = "http://www.youtube.com/watch?v=";
		echo "<div id='file_" . $link["id"] . "'><span class=link_actions></span> <span class='link_main'>";
		if ($link["name"] == "") echo "<a href='about:blank' style='color: red;'>NO NAME</a>";
		else if ($link["target"] == "") echo "<a href='about:blank' style='color: red;'>" . $link["name"] . "</a>";
		else echo "<a href='$prefix" . $link["target"] . "' target='_blank'>" . $link["name"] . "</a>";
		echo "</span></div>";
	}
	echo "</div>";
}
?>
