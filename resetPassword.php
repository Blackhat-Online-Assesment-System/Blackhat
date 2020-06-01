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

// Coming from Email
if(isset($_GET['hash']) && isset($_GET['email'])){
	$email = $_GET['email'];
	$hash = $_GET['hash'];
	$sql = "SELECT * FROM `Users` WHERE `Email` = '" . SanitizeSQLEntry($_GET['email']) . "' AND `ResetPasswordHash` = '" . SanitizeSQLEntry($_GET['hash']) . "' AND `ResetPasswordExpTime` >= CURRENT_TIMESTAMP()";
	$stm = $DatabaseConnection->prepare($sql);
    $stm->execute();
    $records = $stm->fetchAll();
    $row_count = $stm->rowCount();
    if($row_count > 0){
    	// Check for 2FA
    	if($records[0]["TwoFactorEnabled"] == '1' && $_SESSION['2FAPassed'] != true){
    		// Stop running and switch to the Verify 2FA
    		header('Location: verifyTwoFactorAuth.php?Action=ResetPW&email=' . $_GET['email'] . '&hash=' . $_GET['hash']);
    		exit();
    	} else {
    		$CanReset = true;
    	}
    } else {
    	header('Location: error.php?verifymsg=' . CreateAuthenticatedMessageHash() . '&error=Something went wrong with that request.');
    }
    //Coming from form
} else if(isset($_POST['Hash']) && isset($_POST['Email'])){
	$email = $_POST['Email'];
	$hash = $_POST['Hash'];
	// 1. Check still OK
	$sql = "SELECT * FROM `Users` WHERE `Email` = '" . SanitizeSQLEntry($_POST['Email']) . "' AND `ResetPasswordHash` = '" . SanitizeSQLEntry($_POST['Hash']) . "' AND `ResetPasswordExpTime` >= CURRENT_TIMESTAMP()";
	$stm = $DatabaseConnection->prepare($sql);
    $stm->execute();
    $row_count = $stm->rowCount();
    if($row_count > 0){
    	// 2. Check PW's match
    	if(isset($_POST['Password']) && $_POST['Password'] != "" && ($_POST['Password'] == $_POST['ConfirmPassword']) ){
    		$sql = "UPDATE `Users` SET `Password` = '" . password_hash(SanitizeSQLEntry($_POST['Password']), PASSWORD_DEFAULT) . "', `ResetPasswordHash` = NULL, `ResetPasswordExpTime` = '" . date("Y-m-d H:i:s", strtotime('-24 hours', strtotime(date("Y-m-d H:i:s")))) . "' WHERE `Email` = '" . SanitizeSQLEntry($_POST['Email']) . "'";
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
					<p>Heya " . $records[0]["UserFullName"] . "!<br>Just letting you know your password to Blackhat has been successfully reset. If this was you, great! No further action on your part is needed. If this was not you, please take action to secure your account by resetting your login.<br>Thanks! ~Blackhat Team</p>

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

			  $mail->Subject   = 'Blackhat - Your Password has been Reset';
			  $mail->Body      = $EmailMessage;
			  $mail->Send();
			  } catch (Exception $e) {
			    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo . '<br />';
			};
			header('Location: message.php?Message=PasswordResetOK&MessageVerify=' . CreateAuthenticatedMessageHash());
    	} else {
    		$Message = "Oops - that didn't work, try again and check your passwords match in the boxes!";
    		$CanReset = true;
    	}
    } else {
    	header('Location: error.php?verifymsg=' . CreateAuthenticatedMessageHash() . '&error=Something went wrong with that request.');
    }
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Reset Password | <?php echo $SiteTitle; ?></title>
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
				<h1 class="wst-header-title">Reset Password</h1>
					<br>
				<?php	
					if($CanReset){
				?>
					<form action="resetPassword.php" method="POST" class="wst-login-form">
							<input type="hidden" name="Action" value="RunReset">
							<input type="hidden" name="Hash" value="<?php echo SanitizeSQLEntry($hash); ?>">
							<input type="hidden" name="Email" value="<?php echo SanitizeSQLEntry($email); ?>">
				        New Password: <input type="password" name="Password" placeholder="New Password" required="required"> 
				        New Password: <input type="password" name="ConfirmPassword" placeholder="New Password" required="required"> 
				            <br>
				        <input type="submit" name="submit" value="Submit">
				    </form>
				<?php
					} else {
						header('Location: login.php');
					}
				?>
			</section>
		</main>
	</body>
</html>