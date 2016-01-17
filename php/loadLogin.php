<style>
.spacer { padding-right: 20px; content: '&nbsp;' }
</style>
<div class="span4 block">
	<form class='form-horizontal' onsubmit="return signIn()">
	<div class='control-group'>
		<label class='control-label'>Email</label>
		<div class='controls'><input type="text" class="input-large" id="email" placeholder="gburdell3@gatech.edu" name="email" /></div>
	</div>
	<div class='control-group'>
		<label class='control-label'>Password</label>
		<div class='controls'><input type="password" class="input-large" id="password" placeholder="password" name="password" /></div>
	</div>
	<div class='control-group'>
		<div class='controls'><a href='#editProfile'>Register</a><span class='spacer'></span><a href='#forgotPassword'>Forgot</a><span class='spacer'></span><button type="submit" value="Sign In" class="btn">Sign In</button></div>
	</div>
	</form>
</div>
