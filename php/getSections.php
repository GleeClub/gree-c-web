<?php
require_once('./functions.php');
echo dropdown(sections($_POST['choir']), 'section', $USER ? sectionFromEmail($user) : '');
?>

