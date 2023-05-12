<?php

session_start();
include_once('library/csrf.php');
include_once('library/auth.php');
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';
function signinProcess($email, $password, $flag){
		//echo 'hello world';
		$sql=get_DB();

		$user = uniqid(mt_rand(), true);
		$userPassword=hash_hmac('sha1',$password, $user);

		$q=$sql->prepare('INSERT INTO account (salt, password, flag, email) VALUES (?, ?, ?, ?)');

		$q->execute(array($user, $userPassword, $flag, $email));

		return 1;
}

function changeProcess($email, $password){
		$sql=get_DB();
		$q=$sql->prepare('SELECT * FROM account WHERE email = ?');
		$q->execute(array($email));
		if($r=$q->fetch())
		{
			$userPassword=hash_hmac('sha1', $password, $r['salt']);
			$q=$sql->prepare('UPDATE account SET password = ? WHERE email = ?');
			$q->execute(array($userPassword, $email));
		}
		return 1;
}

function csce5560_login(){
	if (empty($_POST['email']) || empty($_POST['pw']) 
		|| !preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email'])
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['pw']))
		throw new Exception('Wrong Credentials');
	
	$login_success=loginProcess($_POST['email'],$_POST['pw']); //login process is in auth.php
	//echo $login_success;
	if ($login_success == 1){
		
		session_regenerate_id(true);
		
		header('Location: admin.php', true, 302);
		exit();
	}
	else if($login_success == 2){
		
		session_regenerate_id(true);
		
		header('Location: home_page.php', true, 302);
		exit();
	}
	 else
		throw new Exception('User name invalid or user password invalid');
}

function csce5560_logout(){

	setcookie('authtoken','',time()-3600);
	$_SESSION['authtoken']=null;
	setcookie('commontoken','',time()-3600);
	$_SESSION['commontoken']=null;
	echo 'You logout successfully';
	
	header('Location: page_login.php');
	exit();
}


function csce5560_change(){
	if (empty($_POST['email']) || empty($_POST['pw']) 
		|| !preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email'])
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['pw']))
		throw new Exception('Wrong Credentials');
	
	if($_POST['newpw'] == $_POST['pw']){
		throw new Exception('Old password and new password cannot be the same.');
	}

	$login_success=loginProcess($_POST['email'],$_POST['pw']); 
	if (($login_success == 1) || ($login_success == 2)){
		$change_success = changeProcess($_POST['email'],$_POST['newpw']);
		if($change_success)
		{
			session_regenerate_id(true);
			
			header('Location: page_login.php', true, 302);
			csce5560_logout();
			exit();
		}
	}
	 else
		throw new Exception('User name invalid or user password invalid');
}

function csce5560_signin(){
	if (empty($_POST['email']) || empty($_POST['pw']) 
		|| !preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email'])
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['pw']))
		throw new Exception('Wrong Credentials');
	
	if($_POST['repw'] != $_POST['pw']){
		throw new Exception('Password Mismatches');
	}


	$signin_success=signinProcess($_POST['email'],$_POST['pw'],1);

	if ($signin_success){
		
		session_regenerate_id(true);
		
		header('Location: page_login.php', true, 302);
		exit();
	}
	 else
		throw new Exception('Not Able To Sign Up');
	
}

function csce5560_forget(){
/*	$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = gethostname();
$mail->SMTPAuth = true;
$mail->Username = 'abdularbaz708@yahoo.com';
$mail->Password = 'Akareem708';
$mail->setFrom('abdularbaz708@yahoo.com');
$mail->addAddress('abdularbaz708@gmail.com');
$mail->Subject = 'Here is the subject';
$mail->Body    = 'This is the body.';
$mail->send();
echo ' hel';*/
	$sql=get_DB();


	if (isset($_POST["ForgotPassword"])) {
	
		
		if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
			$email = $_POST["email"];
		}
		else{
			throw new Exception('Email is not valid');
		}

		
		$q=$sql->prepare('SELECT * FROM account WHERE email = ?');
		$q->execute(array($email));
		
		if ($r=$q->fetch())
		{
			
			$user = $r["salt"];
			$nounce = hash_hmac('sha1', $email, $user);

		
			$pwrurl = "https://s56.ierg4210.ie.cuhk.edu.hk/reset_password.php?q=".$nounce;
			
			
			$mailbody = "Dear user,\n\nIf this e-mail does not apply to you please ignore it. It appears that you have requested a password reset at our website www.yoursitehere.com\n\nTo reset your password, please click the link below. If you cannot click it, please paste it into your web browser's address bar.\n\n" . $pwrurl . "\n\nThanks,\nThe Administration";
			$headers = "From: webmaster@example.com" . "\r\n" .
			$m = mail("abdularbaz708@yahoo.com","Password Reset",$mailbody);
			echo $m,"Your password recovery key has been sent to your e-mail address.";
			
		}
		else
			echo "No user with that e-mail address exists.";
	}	
}



try {
	
	if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action']))
		throw new Exception('Undefined Action');
	
	
	if ($_REQUEST['action']=='login')
		verify_csrf($_REQUEST['action'], $_POST['nonce']);
	
	
	if (($returnVal = call_user_func('csce5560_' . $_REQUEST['action'])) == false) {
		if ($sql && $sql->errorCode()) 
			error_log(print_r($sql->errorInfo(), true));
		throw new Exception('Failed');
	} 
    

} catch(PDOException $e) {
	error_log($e->getMessage());
	header('Refresh: 3; url=page_login.php?error=db');
	echo '<strong>Error Occurred:</strong> DB <br/>Redirecting to login page in 3 seconds...';
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
} catch(Exception $e) {
	header('Refresh: 3; url=page_login.php?error=' . $e->getMessage());
	echo '<strong>Error Occurred:</strong> ' . $e->getMessage() . '<br/>Redirecting to login page in 3 seconds...';
}
?>