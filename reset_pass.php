<?php
include_once('library/csrf.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Password Change</title>
    <script src="https://smtpjs.com/v3/smtp.js"></script>  
</head>
<body>
	<form method="POST" action="authenticator.php?action=<?php echo ($action = 'forget'); ?>">
		Please provide your email address:<br> <input name="email" type="text" size="15" required="true" pattern="^[\w=+\-\/][\w=\'+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$"/>
		<input name="nonce" type="hidden" value="<?php echo get_csrf($action); ?>"/> 
		<input name="ForgotPassword" type="submit"value=" Request Reset "/>
	</form>
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
