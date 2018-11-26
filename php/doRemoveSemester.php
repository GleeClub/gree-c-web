<?php
require_once('functions.php');
if (! $USER || ! hasPermission("edit-semester")) err("DENIED");
query("delete from `semester` where `semester` = ? limit 1", [$name]);
echo "<br><h3>Removal Results</h3><br>$name was removed from the database.<br>";
?>
