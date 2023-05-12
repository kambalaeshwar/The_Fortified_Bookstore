<?php
include_once('library/csrf.php');
include_once('library/auth.php');

if ($_SESSION['authtoken']){
	header('Location: admin.php');
	exit();
}
else if($_SESSION['commontoken']){
	header('Location: home_page.php');
	exit();
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Login Panel</title>
</head>
<body>
<h3>Login or Sign Up</h3>
<fieldset>
	<legend>Login</legend>
	<form id="loginForm" method="POST" action="authenticator.php?action=<?php echo ($action = 'login'); ?>">
		<label for="email">Email:</label>
		<div>
		<input type="text" name="email" required="true" pattern="^[\w=+\-\/][\w=\'+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$" />
		</div>
		<label for="pw">Password:</label>
		<div>
		<input type="password" name="pw" required="true" pattern="^[\w@#$%\^\&\*\-]+$" />
		</div>
		<input type="hidden" name="nonce" value="<?php echo get_csrf($action); ?>"/>
		<input type="submit" value="Login" />
		<input type="button" value="Forget Password" onclick="javascript:location.href='reset_pass.php'"/>
	</form>
</fieldset>

<fieldset>
	<legend>Sign Up</legend>
	<form id="signinForm" method="POST" action="authenticator.php?action=<?php echo ($action = 'signin'); ?>">
		<label for="email">Email:</label>
		<div>
		<input type="text" name="email" required="true" pattern="^[\w=+\-\/][\w=\'+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$" />
		</div>
		<label for="pw">Password:</label>
		<div>
		<input type="password" name="pw" required="true" pattern="^[\w@#$%\^\&\*\-]+$" />
		</div>
		<label for="pw">Repeat Password:</label>
		<div>
		<input type="password" name="repw" required="true" pattern="^[\w@#$%\^\&\*\-]+$" />
		</div>
		<input type="hidden" name="nonce" value="<?php echo get_csrf($action); ?>"/>
		<input type="submit" value="Sign Up" />
	</form>
</fieldset>

</body>
</html>

<style>
    body{background-color: black;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 40px;
        color: azure;}
        form {background-color:black;
            color: white;}
    input[type=text] , input[type = password]{
  width: 50%;
  padding: 12px 20px;
  font-size: 30px;
  margin: 8px 0;
  box-sizing: border-box;
  border-radius: 12px;
}
input[type=button], input[type=submit], input[type=reset] {
  background-color: #04AA6D;
  border: none;
  color: white;
  padding: 16px 32px;
  font-size: 30px;
  text-decoration: none;
  margin: 4px 2px;
  cursor: pointer;
  border-radius: 12px;
}
</style>