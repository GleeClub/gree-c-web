<?php
require_once('functions.php');

if(getuser()){
	$userEmail = mysql_real_escape_string(getuser());
}
else{
	echo '
	<ul class="nav">
		<li>
			<a class="brand" href="../index.php">Greasy Web</a>
		</li>
		<li class="divider-vertical"></li>
	</ul>';
}

?>
