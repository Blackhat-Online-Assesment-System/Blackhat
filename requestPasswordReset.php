<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File resetPassword.php
* Allows users to reset their Password
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

// Request the Reset!
if(isset($_POST['email'])){
	// If the user has submitted the login form, we should check if email exists, if so make a new "Hash" and send an email
    //Protect our inputs from SQL Injection first
    $email = SanitizeSQLEntry($_POST['email']);

    //Check Database if a user exists
    $sql = "SELECT * FROM `Users` WHERE `Email` = '" . $email . "' LIMIT 1";
    $stm = $DatabaseConnection->prepare($sql);
    $stm->execute();
    $records = $stm->fetchAll();
    $row_count = $stm->rowCount();

    //Now check if a user exists and that the password matches
    if($row_count > 0){
    	$ResetPasswordHash = CreateAuthenticatedMessageHash();
    	$sql = "UPDATE `Users` SET `ResetPasswordHash` = '" . $ResetPasswordHash . "', `ResetPasswordExpTime` = '" . date("Y-m-d H:i:s", strtotime('+24 hours', strtotime(date("Y-m-d H:i:s")))) . "' WHERE `Email` = '" . $email . "'";
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
						<p>Heya " . $records[0]["UserFullName"] . "!<br>Someone has requested a password reset for your Blackhat account. If this was you, use the link below to reset, <strong>If this was NOT you that requested the reset, your password has NOT been reset, simply delete this email and move along. <br>Thanks for using Blackhat!</p>
							<br>
							<br>
						<div class=\"ConfirmationButtonDiv\"><a class=\"ActivationLink\" href=\"https://blackhat.bensommer.net/resetPassword.php?hash=" . $ResetPasswordHash . "&email=" . $records[0]["Email"] . "\"><button class=\"ConfirmationButton\">Reset Password</button></a></div>
							<br>
						Link not working? Copy and paste this to your URL bar instead: <br> https://blackhat.bensommer.net/resetPassword.php?hash=" . $ResetPasswordHash . "&email=" . $records[0]["Email"] . "

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
			  $mail->addAddress($records[0]["Email"]);   

			  //Content
			  $mail->isHTML(true); // Set email format to HTML

			  $mail->Subject   = 'Blackhat - Password Reset';
			  $mail->Body      = $EmailMessage;
			  $mail->Send();
			  } catch (Exception $e) {
			    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo . '<br />';
			};
		$Message = "If an account with that email was found, you'll get a reset link in your email shortly. Thanks!";
    }
};
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title> Request Password Reset | <?php echo $SiteTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="assets/css/main.css?version=15">
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
				<h2 class="wst-success-notice"><?php if(isset($Message)) echo $Message; ?></h2>
					<br>
				<h1 class="wst-header-title">Request Password Reset</h1>
						<br>
					<p>Forgot your password? No worries! Enter your email below and if an account with that email is registered we'll send you a reset link via email!</p>
						<br>
				    <form action="requestPasswordReset.php" method="POST" class="wst-login-form">
				        Email Address: <input type="email" name="email" placeholder="Email Address" required="required"> 
				            <br>
				        <input type="submit" name="submit" value="Submit">
				    </form>
				    	<br>
				    	<br>
				    	<a href="login.php" class="wst-button"><< Back to Login</a>
			</section>
		</main>
	</body>
</html>