<?php
require_once('php/functions.php');
$userEmail = getuser();

function actionOptions($userEmail)
{
	$type = positionFromEmail($userEmail);
	$officerOptions = '';
	if (isOfficer($userEmail))
	{
		$officerOptions .= '
			<li><a href="#event">Add/Remove Event</a></li>
			<li><a href="#addAnnouncement">Make an Announcement</a></li>';
	}
	if ($type == "Treasurer" || isUber($userEmail))
	{
		$officerOptions .= '
			<li><a href="#money">Add Transactions</a></li>';
	}
	if (isUber($userEmail))
	{
		$officerOptions .= '
			<li><a href="timeMachine">Look at Past Semesters</a></li>
			<li><a href="#absenceRequest">Absence Requests</a></li>
			<li><a href="#ties">Ties</a></li>
			<li><a href="#semester">Edit Semester</a></li>
			<li><a href="#officers">Edit Officers</a></li>
			<li><a href="#doclinks">Edit Document Links</a></li>';
	}
	echo $officerOptions;
}

if ($_SERVER['HTTP_HOST'] != $domain) header("Location: $BASEURL");
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
			<?php if ($userEmail) { ?>
			<li><a href="#chatbox">Chatbox</a></li>
			<li><a href="#messages" >Messages <?php if ($userEmail) echo '<span class="label" id="unreadMsgs">' . getNumUnreadMessages(getuser()) . '</span>';?></span></a></li>
			<li class="divider-vertical"></li>
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">Events <b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="#allEvents">All</a></li>
					<li><a href="#rehearsal">Rehearsal</a></li>
					<li><a href="#sectional">Sectional</a></li>
					<li><a href="#tutti">Tutti</a></li>
					<li><a href="#volunteer">Volunteer</a></li>
					<?php if (isOfficer($userEmail)) { ?> <li><a href="#pastEvents">Everything Ever</a></li> <?php } ?>
				</ul>
			</li>
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="#feedback">Feedback</a></li>
					<li><a href="#suggestSong">Suggest a song</a></li>
					<li><a href="#roster">Members</a></li>
					<?php if ($userEmail) actionOptions($userEmail); ?>
				</ul>
			</li>
			<?php } ?>
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">Documents <b class="caret"></b></a>
				<ul class="dropdown-menu">
					<?php if ($userEmail) { ?><li><a href="#repertoire">Repertoire</a></li><?php } ?>
					<li><a href="#minutes">Meeting Minutes</a></li>
					<li><a href="#syllabus">Syllabus</a></li>
					<li><a href="#handbook">GC Handbook</a></li>
					<li><a href="#constitution">GC Constitution</a></li>
				</ul>
			</li>
			<li class="divider-vertical"></li>
			<?php if ($userEmail) { ?>
			<li>
				<form class="navbar-search pull-left">
					<input type="text" class="search-query" data-provide="typeahead"  data-items="4" data-source='["Taylor","Drew","Tot"]'>
				</form>
			</li>
			<?php } ?>
		</ul>
		<ul class="nav pull-right">
		<?php if ($userEmail) { ?>
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"> <?php echo getuser(); ?> <b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="#editProfile">My Profile</a></li>
					<li><a href="php/logOut.php">Log Out</a></li>
				</ul>
			</li>
		<?php } ?>
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
		    <p>Will you be in the Glee Club this semester?  If not, hit Close and you will still be able to view the site, but you won't be assessed dues or expected at events.  If you are returning, please verify the information below, then hit Confirm to confirm your account.</p>
		<form class="form-horizontal">
		    <div class="control-group">
			<label class="control-label" style='font-weight: bold'>Registration:</label>
			<div class="controls"><div class="btn-group" data-toggle="buttons-radio"><button type="button" class="btn" id="confirm_class">Class</button><button type="button" class="btn" id="confirm_club">Club</button></div></div>
		    </div>
		    <div class="control-group">
			<label class="control-label" style='font-weight: bold'>Location:</label>
			<div class="controls"><input type="text" id="confirm_location"></div>
		    </div>
		</form></div>
		<div class="modal-footer">
		    <a href="#" class="btn" style="color: inherit" data-dismiss="modal">Close</a>
		    <a href="#" class="btn btn-primary" style="color: inherit" onclick="confirm_account()">Confirm</a>
		</div>
	</div>

	<?php
		if ($userEmail != '')
		{
			$sql = "select UNIX_TIMESTAMP(semester.end) as end from semester,variables where semester.semester=variables.semester";
			$arr = mysql_fetch_array(mysql_query($sql));
			$semesterEnd = $arr['end'];

			if (positionFromEmail($userEmail) == "President" && time() > $semesterEnd) echo newSemesterModal();
			else
			{
				//if the user is not confirmed for the semester, prompt them to confirm
				$arr = mysql_fetch_array(mysql_query("SELECT `location` FROM `member` WHERE `email` = '$userEmail'"));
				$confirmed = mysql_num_rows(mysql_query("select `member` from `activeSemester` where `member` = '$userEmail' and `semester` = '$CUR_SEM'"));
				if (! $confirmed)
				{
					$loc = addslashes($arr['location']);
					echo '<script>
						$("#confirm_location").prop("value", "' . $loc . '");
						$("#confirmModal").modal();
					</script>';
				}
			}
		}
	?>
</body>
</html>
