<?php
	require_once('php/functions.php');
	if(isset($_COOKIE['email'])){
		$userEmail = $_COOKIE['email'];
	}
	else{
		header("Location: ../");
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
	<!--<script src=""https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js""></script> -->
	<script src="js/jquery-1.7.2.js"></script>
	<script src="js/jquery-ui-1.8.22.custom.min.js"></script>
	<script src="bootstrap/js/bootstrap.js"></script>
	<script src="bootstrap/js/bootstrap-tab.js"></script>
	<script src="bootstrap/js/bootstrap-dropdown.js"></script>
	<script src="bootstrap/js/bootstrap-button.js"></script>
	<script src="bootstrap/js/bootstrap-modal.js"></script>
	<link href="css/style.css" rel="stylesheet">

	<!-- Stuff for the tokenizer in messages -->
	<script type="text/javascript" src="css/token-js/src/jquery.tokeninput.js"></script>
	<link rel="stylesheet" type="text/css" href="css/token-js/styles/token-input.css" />
	<link rel="stylesheet" type="text/css" href="css/token-js/styles/token-input-facebook.css" />
	
	<script src="js/main.js"></script>
	<title>Gree-Cier-Web</title>
</head>
<body>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="navbar navbar-fixed-top">
			  <div class="navbar-inner">
				<div class="container">
					<ul class="nav">
						<li>
							<a class="brand" href="../index.php">Greasy Web</a>
						</li>
						<li class="divider-vertical"></li>
					 	<li class="divider-vertical"></li>
					</ul>
					<ul class="nav pull-right">
					<?php
						if(isset($_COOKIE['email'])) {
							echo "<li><br>Logged in as " . $_COOKIE['email'] . "</li>";
							//Continuing if
					?>
						<li class="divider-vertical"></li>
						<li>
							<a href="php/logOut.php">Log Out</a>
						</li>
				 	</ul>
				 	<?php
				 		} //Endif
				 	?>
				</div>
				<div class="span11 block" id="timeMachineTabs">
					<ul class="nav nav-pills">
						<li class="active">
							<a href="#events">Events</a>
						</li>
					 	<li>
							<a href="#attendance">Attendance</a>
						</li>
					 	<li>
							<a href="#money">Money</a>
						</li>
						<li>
							<a href="#members">Members</a>
						</li>
					</ul>
					<div class="span11" id="main">
					</div>
				</div>
			  </div>
			</div>
		</div>
	</div>

	<script>
		if(window.location.hash==''){
			$("#main").html('<img data-value="images/1.130201.jpg" src="images/1.130201.jpg">');
		}
	</script>
</body>
</html>
