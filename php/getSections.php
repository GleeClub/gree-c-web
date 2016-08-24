<?php
require_once('./functions.php');
echo dropdown(sections(mysql_real_escape_string($_POST['choir'])), 'section', $USER ? sectionFromEmail($user) : '');
?>

