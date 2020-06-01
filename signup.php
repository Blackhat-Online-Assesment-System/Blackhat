<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File signup.php
* Allows users to signup for a new account
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(true, null);

// Include Mailer Headers
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'mailer/src/Exception.php';
require 'mailer/src/PHPMailer.php';
require 'mailer/src/SMTP.php';

/*
* Allow a user to signup for the system
* 1st Check email isn't already used.
* If not, let's make a user and then send an email confirmation to him to check it's a real email
* Then once he confirms, he can login!
*/
if(isset($_POST['Email']) && isset($_POST['Password']) && isset($_POST['PasswordVerify']) && isset($_POST['AccountType']) && isset($_POST['Name'])){
    //Protect our inputs from SQL Injection first
    $Name = SanitizeSQLEntry($_POST['Name']);
    $Email = SanitizeSQLEntry($_POST['Email']);
    $Password = SanitizeSQLEntry($_POST['Password']);
    $PasswordVerify = SanitizeSQLEntry($_POST['PasswordVerify']);
    $AccountType = SanitizeSQLEntry($_POST['AccountType']);
    
    //Check Database if a user exists
    $sql = "SELECT * FROM `Users` WHERE `Email` = '" . $Email . "' LIMIT 1";
    $stm = $DatabaseConnection->prepare($sql);
    $stm->execute();
    $records = $stm->fetchAll();
    $row_count = $stm->rowCount();
    $ShowError = false;
    $ErrorMessage = "";
    if($row_count > 0){
    	//Account already exists, stop and throw an error
    	$ShowError = true;
    	$ErrorMessage = "Oops, looks like that email is already being used, please try logging in instead or if you forgot your password <a href=forgotPassword.php>click here to reset it</a>";
    } else if($Password != $PasswordVerify){
    	//Check passwords match
    	$ShowError = true;
    	$ErrorMessage = "Oops, please check your password matches correctly.";
    } else {
    	//Make an account!
    	$AccountActivateHash = CreateAuthenticatedMessageHash();
    	$sql = "INSERT INTO `Users` (UserFullName, Email, Password, UserRoles, AccountVerified, VerifyHash) VALUES ('" . $Name . "', '" . $Email . "', '" . password_hash($Password, PASSWORD_DEFAULT) . "', '" . $AccountType . "', '0', '" . $AccountActivateHash . "')";
    	$stm = $DatabaseConnection->prepare($sql);
    	$stm->execute();
    	
			$EmailMessage = "
				<!DOCTYPE HTML>
				<html>
				<style>

					body {
					background: linear-gradient(-45deg, #000000, #696969, #808080, #A9A9A9);
					background-size: 400% 400%;
					animation: gradient 15s ease infinite;
					}

					@keyframes gradient {
						0% {
							background-position: 0% 50%;
						}
						50% {
							background-position: 100% 50%;
						}
						100% {
							background-position: 0% 50%;
						}
					}

					h1 {
						font-family: \"Arial\";
						color: white;
						text-align: center;
					}
					.ConfirmationButtonDiv {
						text-align: center;
					}
					.ConfirmationButton {
						background-color: black;
						border: none;
						color: white;
						padding: 15px 32px;
				  		text-align: center;
				  		text-decoration: none;
				  		display: inline-block;
				  		font-size: 16px;
					}
					.ConfirmationButton:hover {
						background-color: grey;
						color: black;
						cursor: pointer;
					}

					.ActivationLink {
						color: white;
					}
					.ActivationLink:hover {
						color: black;
					}
				</style>
				<body>
					<h1>Welcome to BlackHat! Thank you for trusting us with your scholastic needs!</h1>
					<br><br><br>

					<!-- We can put the website logo here (assuming we have one)-->
						<p>Thanks for signing up for BlackHat! There's only one more step to go! Click on the link below to confirm your account.</p>
							<br>
							<br>
						<div class=\"ConfirmationButtonDiv\"><a class=\"ActivationLink\" href=\"https://blackhat.bensommer.net/activateAccount.php?hash=" . $AccountActivateHash . "\"><button class=\"ConfirmationButton\">Click here to activate your account!</button></a></div>
							<br> 
						Link not working? Copy and paste this to your URL bar instead: <br> https://blackhat.bensommer.net/activateAccount.php?hash=" . $AccountActivateHash . "
					
				</body>
				</html>";

			try{
			  $mail = new PHPMailer();
				$mail->SMTPDebug = $SMTP_Debug;
				$mail->isSMTP();                            
				$mail->Host = $SMTP_Host;                       
				$mail->SMTPAuth = $SMTP_Auth;                     
				$mail->Username = $SMTP_Auth;                      
				$mail->Password = $SMTP_Password;                   
				$mail->SMTPSecure = $SMTP_Security;                     
				$mail->Port = $SMTP_Port;                       
				//Recipients
				$mail->setFrom($SMTP_SendEmailsFromAddress, $SiteTitle);                         
			  $mail->addAddress($Email);   
			  
			  //Content
			  $mail->isHTML(true); // Set email format to HTML
			  
			  $mail->Subject   = 'Welcome to Blackhat!';
			  $mail->Body      = $EmailMessage;

			  $mail->Send();
			  } catch (Exception $e) {
			    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo . '<br />';
			};
    	header('Location: message.php?Message=AccountCreated&MessageVerify=' . CreateAuthenticatedMessageHash());
    }
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Signup | <?php echo $SiteTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="assets/css/main.css?version=16">
		<?php require_once('views/headerAssets.php');?>
		
	</head>
	<body>
		<header class="flex-container wst-header">
			<section class="wst-menu-logo-area">
				<h1><?php echo $SiteTitle; ?></h1>
			</section>
			<section>
				<?php
				    // Get the Top Menu Bar
				    // Note, we allow menu bar to be shown, as clicking on the links, only goes back to login
				    require_once('views/loginMenu.php');
				?>
			</section>
		</header>
		<main>
			<section class="full-width-content wst-main-content align-center">
			    <form action="signup.php" method="POST" class="wst-login-form">
			        <h1 class="wst-header-title">Signup</h1>
			        <h3 class="wst-error-notice"><?php if(isset($ShowError) && $ShowError) { echo $ErrorMessage; } ?></h3>
			            <br>
			        Full Name: <input type="text" name="Name" placeholder="Name" required="required"> 
			        	<br>
			        Email Address: <input type="email" name="Email" placeholder="Email Address" required="required"> 
			            <br>
			        Password: <input type="password" name="Password" placeholder="Password" required="required">
			            <br>
			        Confirm Password: <input type="password" name="PasswordVerify" placeholder="Password" required="required">
			        	<br>
			        I am a: <select name="AccountType">
			        			<option value="student">Student</option>
			        			<option value="instructor">Instructor</option>
			        		</select>
			        	<br>
			        <input type="submit" name="submit" value="Signup">
			    </form>
			    	<br>
			    	<br>
			    <h3>Already have an account?</h3>
			    	<br>
			    	<a href="login.php" class="wst-button">Click here to login!</a>
			</section>
		</main>
	</body>
	</html>