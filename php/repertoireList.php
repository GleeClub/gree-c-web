<div class="span3 block" id=repertoire_list><?php
require_once('functions.php');
if (! $CHOIR) die("Not logged in"); # FIXME
$results = mysql_query("select `id`, `title` from `song` where `current` = 1 and `choir` = '$CHOIR' order by `title` asc");
if (! $results) die("Database query failed.");
if ($USER && isOfficer($USER)) echo "<div style=\"padding-top: 5px\"><button class=btn style=\"padding: 5px; width: 100%\" id=repertoire_add>Add Song...</button></div>";
echo "<style>td.repertoire_head { font-size: 12pt; font-weight: bold; }</style>";
echo "<table class=\"table\" id=repertoire_table>";
if (mysql_num_rows($results)) echo "<tr><td class=repertoire_head>Current Repertoire</td></tr>";
while ($result = mysql_fetch_array($results)) echo "<tr><td id=\"row_$result[0]\" class=repertoire_row>$result[1]</td></tr>";
if (mysql_num_rows($results)) echo "<tr><td class=repertoire_head>Other Repertoire</td></tr>";
$results = mysql_query("select `id`, `title` from `song` where `current` = 0 and `choir` = '$CHOIR' order by `title` asc");
if (! $results) die("Database query failed.");
while ($result = mysql_fetch_array($results)) echo "<tr><td id=\"row_$result[0]\" class=repertoire_row>$result[1]</td></tr>";
echo "</table>";
?></div>
<div class="span8 block" id=repertoire_main>Select a song to the left.</div>
<div class="modal hide fade" id=confirm_delete_song>
<div class="modal-header" id=confirm_delete_song_head>Confirm Delete</div>
<div class="modal-body" id=confirm_delete_song_body>Are you sure you want to delete this song?  All links associated with it will be removed, and any files associated with it stored on the server will be deleted.</div>
<div class="modal-footer" id=confirm_delete_song_footer><button class=btn id="delete_song_deny">No</button> <button class="btn btn-primary" id="delete_song_confirm">Yes</button></div>
</div>
<div class="modal hide fade" id=song_editor>
<div class="modal-header" id=song_editor_head>Edit Song</div>
<div class="modal-body" id=song_editor_body>
Name:  <input type=text id=song_edit_name><br>
Description:  <textarea id=song_edit_desc></textarea>
</div>
<div class="modal-footer" id=song_editor_footer><button class=btn id="edit_song_cancel">Cancel</button> <button class="btn btn-primary" id="edit_song_accept">Save</button></div>
</div>
</div>

