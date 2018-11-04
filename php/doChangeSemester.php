<?php
require_once('functions.php');

if (! $USER || ! hasPermission("edit-semester")) die("DENIED");

query("update `variables` set `semester` = ?", [$_POST["name"]]);
$res = query("select `semester` from `variables`", [], QONE);
if (! $res) die("Failed to fetch semester from variables");
$cursem = $res["semester"];
echo "<br><h3>Semester Change Results</h3><br>The current semester is now: $cursem";
?>
