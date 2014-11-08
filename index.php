<?php
require_once('php/functions.php');
if(isset($_COOKIE['email'])){
	$userEmail = $_COOKIE['email'];
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
}

function actionOptions($userEmail){
	$type = positionFromEmail($userEmail);
	$officerOptions = '';
	if(($type == "VP") || ($type == "President"))
	{
		$officerOptions .= '
			<li><a href="#absenceRequest">Absence Requests</a></li>
			<li><a href="#ties">Ties</a></li>';
	}
	if(isOfficer($userEmail))
	{
		$officerOptions .= '
			<li><a href="#event">Add/Remove Event</a></li>
			<li><a href="#addAnnouncement">Make an Announcement</a></li>
			<li><a href="../timeMachine">Look at Past Semesters</a></li>';
	}
	if($type == "President")
	{
		$officerOptions .= '
			<li><a href="#semester">Add/Remove/Change Semester</a></li>';
	}
	echo $officerOptions;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="bootstrap/css/bootstrap.css" rel="stylesheet">
	<!--<script src=""https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js""></script> -->
	<script src="js/jquery-1.7.2.js"></script>
	<script src="js/jquery-ui-1.8.22.custom.min.js"></script>
	<script src="bootstrap/js/bootstrap.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>
	<link href="css/style.css" rel="stylesheet">
	<link href="css/datepicker.css" rel="stylesheet">

	<!-- Stuff for the tokenizer in messages -->
	<script type="text/javascript" src="css/token-js/src/jquery.tokeninput.js"></script>
	<link rel="stylesheet" type="text/css" href="css/token-js/styles/token-input.css" />
	<link rel="stylesheet" type="text/css" href="css/token-js/styles/token-input-facebook.css" />
	
	<script src="js/main.js"></script>
	<title>Gree-C-Web</title> <!-- retro -->
</head>
<body>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="navbar navbar-fixed-top navbar-inverse" style="font-size: 13px">
			  <div class="navbar-inner">
				<div class="container">
					<ul class="nav">
						<li><a class="brand" href="index.php">Greasy Web</a></li>
						<li class="divider-vertical"></li>
						<li><a href="#chatbox">Chatbox</a></li>
					<li><a href="#messages" >Messages <?php if(isset($_COOKIE['email'])) echo '<span class="label" id="unreadMsgs">' . getNumUnreadMessages($_COOKIE['email']) . '</span>';?></span></a></li>
					<li class="divider-vertical"></li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Events <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="#allEvents">All</a></li>
							<li><a href="#rehearsal">Rehearsal</a></li>
							<li><a href="#sectional">Sectional</a></li>
							<li><a href="#tutti">Tutti</a></li>
							<li><a href="#volunteer">Volunteer</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="#feedback">Feedback</a></li>
							<li><a href="#roster">Members</a></li>
							<li><a href="#suggestSong">Suggest a song</a></li>
							<?php if(isset($_COOKIE['email'])) actionOptions($userEmail); ?>
						</ul>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Documents <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="#repertoire">Repertoire</a></li>
							<li><a href="#syllabus">Syllabus</a></li>
							<li><a href="#minutes">Meeting Minutes</a></li>
							<li><a href="#handbook">GC Handbook</a></li>
							<li><a href="#constitution">GC Constitution</a></li>
						</ul>
					</li>
					<li class="divider-vertical"></li>
					<li>
						<form class="navbar-search pull-left">
						<input type="text" class="search-query" data-provide="typeahead"  data-items="4" data-source='["Taylor","Drew","Tot"]'>
						</form>
					</li>
					</ul>

					<ul class="nav pull-right">
					<?php
						if(isset($_COOKIE['email']))
						{ ?>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown"> <?php echo $_COOKIE['email']; ?> <b class="caret"></b></a>
								<ul class="dropdown-menu">
									<li><a href="#editProfile">My Profile</a></li>
									<li><a href="php/logOut.php">Log Out</a></li>
								</ul>
							</li>
						<?php } //Endif
					?>
					</ul>
				</div>
			  </div>
			</div>
			<div class="span11 block" id="main" style='margin-bottom: 100px'></div>
		</div>
	</div>
	
	<?php /* This is the prompt shown if the user's account is not confirmed */ ?>
	<div class="modal hide fade" id='confirmModal'>
		<div class="modal-header">
		 	<button type="button" class="close" data-dismiss="modal">×</button>
		    <h3>Confirm your account for this semester!</h3>
		</div>
		<div class="modal-body">
		    <p>If you plan on being in the Glee Club this semester, just click Confirm below!  If not, you can still hit Close and view the site, but we won't expect you at gigs/rehearsal or charge you dues.  If you drop the class and want to unconfirm your account, just let one of the officers know.</p>
		</div>
		<div class="modal-footer">
		    <a href="#" class="btn" data-dismiss="modal">Close</a>
		    <a href="#" class="btn btn-primary" data-dismiss="modal" onClick="doConfirmAccount();">Confirm</a>
		</div>
	</div>

	<?php
		if(isset($_COOKIE['email'])) {
			$email = mysql_real_escape_string($_COOKIE['email']);
			
			//check if the user is the President and if the current semester in the database is accurate.  The President might need to be prompted to change the semester.
			$sql = "select position from member where email='$email'";
			$arr = mysql_fetch_array(mysql_query($sql));
			$position = $arr['position'];

			$sql = "select UNIX_TIMESTAMP(validSemester.end) as end from validSemester,variables where validSemester.semester=variables.semester";
			$arr = mysql_fetch_array(mysql_query($sql));
			$semesterEnd = $arr['end'];

			if($position=='President' && time()>$semesterEnd){
				echo newSemesterModal();
			}
			else{
				//if the user is not confirmed for the semester, prompt them to confirm
				$sql = "SELECT confirmed FROM member WHERE email='$email'";
				$arr = mysql_fetch_array(mysql_query($sql));
				if(!$arr['confirmed']) {
					echo '
					<script>$("#confirmModal").modal()</script>';
				}
			}
		}
	?>
</body>
</html>
