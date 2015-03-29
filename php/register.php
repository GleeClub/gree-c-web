<?php
	require_once('./functions.php');
?>

<html>
<head>
	<style>
		#submit{
			text-align: left;	
		}

		#title{
			font-size:30px;
			text-align: center;
		}

		#buttons{
			text-align: left;
		}

		#officerOptions{
			text-align: center;
		}
		#center{
			text-align: center;
		}

		#form{
			table-layout: fixed;
			word-wrap: break-word;
		}

		#cellwrap{
			width: 200px;
		}
		#commentsCell{
			width: 150px;
		}

		#right{
			text-align: right;
		}

		#left{
			text-align: left;
		}

		#headings{
			font-weight: bold;	
		}

		.thingy
		{
			background-color: #FFFFFF;
		}

		tr.topborder td {
			border-top: 1pt solid black;
		}
	</style>
</head>


<div class='span11 block'>
<p id="title"> Welcome to the GT Glee Club!  Register below:</p>
<p>Please Note: This registration is not mandatory. If there is any information on this form that you are not willing to provide, you are under no obligation to do so.  Let an officer know.</p>

<form name="input" method="get" id="registerForm">
<table id="right">
<tr><td>First Name*:</td> <td><input type="text" name="firstname" /></td></tr>
<tr><td>Preferred Name:</td><td><input type="text" name="prefname" /></td></tr>
<tr><td>Last Name*:</td><td><input type="text" name="lastname" /></td></tr>
<tr><td>Section*:</td><td id="buttons"><input type="radio" name="section" value="4" /> Tenor 1<br />
<input type="radio" name="section" value="3" /> Tenor 2<br />
<input type="radio" name="section" value="2" /> Baritone<br />
<input type="radio" name="section" value="1" /> Bass</td></tr>
<tr><td>Email*:</td><td><input type="text" name="email" /></td></tr>
<tr><td>Password*:</td> <td><input type="password" name="password" /></td></tr>
<tr><td>Confirm Password*:</td> <td><input type="password" name="passwordCheck" /></td></tr>
<tr><td>Phone Number (Only digits, eg 8007776666)*:</td><td><input type="text" name="phone" /></td></tr>
<tr><td>Picture (a url to a picture of you):</td><td><input type="text" name="picture" /></td></tr>
<tr><td>Are you in the class or the club?*</td> <td id="buttons"><input type="radio" name="enrollment" value="class" /> Class<br />
<input type="radio" name="enrollment" value="0" /> Club</td></tr>
<tr><td>How many passengers can ride in your car? (0 if you don't have a car)*</td><td><input type="text" name="passengers" /></td></tr>
<tr><td>Do you live on campus?*</td><td id="buttons"><input type="radio" name="onCampus" value="1" /> Yes<br />
<input type="radio" name="onCampus" value="0" /> No</td></tr>
<tr><td>Where do you live? (for carpool purposes)</td><td><input type="text" name="location" /></td></tr>
<tr><td>What should we know about you?</td> <td><input type="text" name="about" /></td></tr>
<tr><td>What is your major?*</td><td><input type="text" name="major" /></td></tr>
<tr><td>What is your hometown?*</td><td><input type="text" name="hometown" /></td></tr>
<tr><td>How many years have you been at Tech?</td><td><input type="text" name="techYear" /></td></tr>
<tr><td>How many years have you been in Glee Club?</td><td><input type="text" name="clubYear" /></td></tr>
<tr><td>What is your GChat screenname?</td><td><input type="text" name="gChat" /></td></tr>
<tr><td>What is your Twitter screenname?</td> <td><input type="text" name="twitter" /></td></tr>
<tr><td>How did you hear about Glee Club?</td><td><input type="text" name="gatewayDrug" /></td></tr>
<tr><td>Do you have any conflicts we should know about?</td><td><input type="text" name="conflicts" /></td></tr>
<tr><td></td><td id="submit"><button type="button" onclick="do_register();">Register!</button></td></tr>
</table>
</form>
</div>
</html>
