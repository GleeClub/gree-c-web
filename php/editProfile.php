<?php
require_once('functions.php');
$email = $_COOKIE['email'];

$sql = "select * from member where email='". mysql_real_escape_string($email) . "'";
$res = mysql_fetch_array(mysql_query($sql));

?>
<div class="span6 block">
<table id="right">
<tr><td><h1 style="margin-bottom:20px">Edit Profile</h1></td><td>
<?php if($res['confirmed']) echo "<div class='alert alert-success'>Account confirmed</div>"; else echo "<div class='alert alert-info'>Account unconfirmed</div>";?></td></tr>
<tr><td>First Name*:</td> <td><input type="text" name="firstName" value="<?php echo $res['firstName']; ?>" /></td></tr>
<tr><td>Preferred Name:</td><td><input type="text" name="prefName" value="<?php echo $res['prefName']; ?>" /></td></tr>
<tr><td>Last Name*:</td><td><input type="text" name="lastName" value="<?php echo $res['lastName']; ?>" /></td></tr>
<tr><td>Section*:</td><td id="buttons"><input type="radio" name="section" value="4" <?php if($res['section'] == "4") echo "checked"; ?>/> Tenor 1<br />
<input type="radio" name="section" value="3" <?php if($res['section'] == "3") echo "checked"; ?> /> Tenor 2<br />
<input type="radio" name="section" value="2" <?php if($res['section'] == "2") echo "checked"; ?> /> Baritone<br />
<input type="radio" name="section" value="1" <?php if($res['section'] == "1") echo "checked"; ?> /> Bass</td></tr>
<tr><td>Email*:</td><td><input type="text" name="email" value="<?php echo $email ?>"/></td></tr>
<tr><td>Password*:</td> <td><input type="password" name="password" /></td></tr>
<tr><td>Confirm Password*:</td> <td><input type="password" name="passwordCheck" /></td></tr>
<tr><td>Phone Number (Only digits, e.g. 8007776666)*:</td><td><input type="text" name="phone" value="<?php echo $res['phone']; ?>"/></td></tr>
<tr><td>Picture (a url to a picture of you):</td><td><input type="text" name="picture" value="<?php echo $res['picture']; ?>"/></td></tr>
<tr><td>Are you regsitered for the class?*</td> <td id="buttons"><input type="radio" name="registration" value="1" <?php if($res['registration'] == 1) echo "checked"; ?>/> Yes<br />
<input type="radio" name="registration" value="0" <?php if($res['registration'] == 0) echo "checked"; ?> /> No</td></tr>
<tr><td>How many passengers (<i>aside from yourself</i>) can ride in your car? (0 if you don't have a car)*</td><td><input type="text" name="passengers" value="<?php echo $res['passengers']; ?>" /></td></tr>
<tr><td>Do you live on campus?*</td><td id="buttons"><input type="radio" name="onCampus" value="1"  <?php if($res['onCampus'] == 1) echo "checked"; ?>/> Yes<br />
<input type="radio" name="onCampus" value="0"  <?php if($res['onCampus'] == 0) echo "checked"; ?> /> No</td></tr>
<tr><td>Where do you live? (for carpool purposes)</td><td><input type="text" name="location" value="<?php echo $res['location']; ?>" /></td></tr>
<tr><td>What should we know about you?</td> <td><input type="text" name="about" value="<?php echo $res['about']; ?>"/></td></tr>
<tr><td>What is your major?*</td><td><input type="text" name="major" value="<?php echo $res['major']; ?>"/></td></tr>
<tr><td>What is your minor?</td><td><input type="text" name="minor" value="<?php echo $res['minor']; ?>"/></td></tr>
<tr><td>How many years have you been at Tech?</td><td><input type="text" name="techYear" value="<?php echo $res['techYear']; ?>"/></td></tr>
<tr><td>How many years have you been in Glee Club?</td><td><input type="text" name="clubYear" value="<?php echo $res['clubYear']; ?>"/></td></tr>
<tr><td>What is your hometown?*</td><td><input type="text" name="hometown" value="<?php echo $res['hometown']; ?>"/></td></tr>
<tr><td>What is your GChat screenname?</td><td><input type="text" name="gChat" value="<?php echo $res['gChat']; ?>"/></td></tr>
<tr><td>What is your Twitter screenname?</td> <td><input type="text" name="twitter" value="<?php echo $res['twitter']; ?>"/></td></tr>
<tr><td>How did you hear about Glee Club?</td><td><input type="text" name="gatewayDrug" value="<?php echo $res['gatewayDrug']; ?>" /></td></tr>
<tr><td>Do you have any conflicts we should know about?</td><td><input type="text" name="conflicts"  value="<?php echo $res['conflicts']; ?>"/></td></tr>
<tr><td>Tie Number:</td><td><span class="input input-large uneditable-input"><?php echo $res['tieNum']; ?></span></td></tr>
<tr><td><span id='success' style='display: none; padding: 6px; background: #afa; border: 1px solid #262; color: #262; border-radius: 4px'>Changes saved</span></td><td id="submit"><button class='btn' data-loading-text="Submitting..." id="editProfileSubmit">Save changes</button></td></tr>
</table>
</div>
