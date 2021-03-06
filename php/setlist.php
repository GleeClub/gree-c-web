<?php
require_once('functions.php');
$event = $_POST['event'];
if (hasEventPermission("edit-setlist", $event)) echo "<button type='button' class='btn pull-right' id='set_edit'>Edit</button>";
$query = query("select `song`.`id`, `song`.`title`, `song`.`key`, `song`.`pitch` from `song`, `gigSong` where `gigSong`.`event` = ? and `gigSong`.`song` = `song`.`id` order by `gigSong`.`order` asc", [$event], QALL);
if (count($query) == 0) echo "<div id='set_empty'>No repertoire set for this event</div>";
echo "<style>td, th { padding: 5px 10px; text-align: left; } .delcol { display: none; }</style>";
echo "<table class='tbl' id='set_table'><thead><tr><th class='delcol'> </th><th>#</th><th>Song</th><th>Key</th><th>Starting Pitch</th></tr></thead><tbody>";
$i = 1;
foreach ($query as $row) echo "<tr id='song$i'><td class='delcol'><a href='#' class='set_del'><i class='icon-remove'></i></a></td><td>" . $i++ . "</td><td><a href='#song:" . $row['id'] . "'>" . $row['title'] . "</a></td><td>" . $row['key'] . "</td><td>" . $row['pitch'] . "</td></tr>";
echo "</tbody><tfoot><tr id='add_set_row' style='display: none'><td> </td><td> </td><td colspan='2'><select id='set_new'>";
foreach (query("select `id`, `title` from `song` where `current` = '1' order by `title` asc", [], QALL) as $row) echo "<option value='" . $row['id'] . "'>" . $row['title'] . "</option>";
echo "</select></td><td><button type='button' class='btn' id='set_add_button'>+</button></td></tr>";
echo "</tfoot></table><div id='helpnote' style='display: none; color: gray; font-style: italic'>Drag and drop songs to reorder them in the set list.</div>";
?>
