<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File verifyTwoFactorAuth.php
* Allows Verify Two Factor Authentication
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(true, null);

// Include the files for Google's 2FA
require_once 'PHPGangsta/GoogleAuthenticator.php';

// Check we're in the right place, Pre Login is True, Post is false, and General Login isn't true
if ((!$_SESSION['IsLoggedIn'] && $_SESSION['Pre2FAPassed'] && !$_SESSION['Post2FAPassed']) || ($_REQUEST['Action'] == "ResetPW" && isset($_REQUEST['email']) && isset($_REQUEST['hash']))){
	if(isset($_POST['Code'])){
		if(!$_SESSION['IsLoggedIn'] && $_REQUEST['Action'] == "ResetPW"){
			$Query = "SELECT * FROM `Users` WHERE `Email` = '" . SanitizeSQLEntry($_REQUEST['email']) . "'";
			$stm = $GLOBALS['DatabaseConnection']->prepare($Query);
			$stm->execute();
			$LoggedInUserRecords = $stm->fetchAll()[0];
		} else {
			$LoggedInUserRecords = GetLoggedInUserInfo();
		}
		if(Verify2FA($LoggedInUserRecords["TFASecret"], $_REQUEST["Code"])){
			$_SESSION['2FAEnabled'] = true;
			$_SESSION['Pre2FAPassed'] = true;
			$_SESSION['Post2FAPassed'] = true;
			$_SESSION['IsLoggedIn'] = true;
			if($_REQUEST['Action'] == "Login"){
				header('Location: index.php');
				exit();
			} else if($_REQUEST["Action"] == "ResetPW"){
				$_SESSION['2FAPassed'] = true;
				header('Location: resetPassword.php?email=' . $_REQUEST['email'] . "&hash=" . $_REQUEST['hash']);
				exit();
			}
		} else {
			$_SESSION['2FAEnabled'] = true;
			$_SESSION['Pre2FAPassed'] = true;
			$_SESSION['Post2FAPassed'] = false;
			$_SESSION['IsLoggedIn'] = false;
			$ShowError = true;
		}
	}
} else {
	header('Location: login.php');
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Login | <?php echo $SiteTitle; ?></title>
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
				    require_once('views/menu.php');
				?>
			</section>
		</header>
		<main>
			<section class="full-width-content wst-main-content align-center">
			    <form action="verifyTwoFactorAuth.php" method="POST" class="wst-login-form">
			        <h1 class="wst-header-title">Verify Two Factor Authentication</h1>
			        <h3 class="wst-error-notice"><?php if(isset($ShowError) && $ShowError) { echo "Error, Authentication Failed, please try again!"; } ?></h3>
			            <br>
			        Code: <input type="text" name="Code" placeholder="Code" required="required"> 
			        		<input type="hidden" name="Action" value="<?php echo $_REQUEST['Action']; ?>">
			        		<input type="hidden" name="email" value="<?php echo $_REQUEST['email']; ?>">
			        		<input type="hidden" name="hash" value="<?php echo $_REQUEST['hash']; ?>">
			            <br>
			        <input type="submit" name="submit" value="Login">
			    </form>
			</section>
		</main>
	</body>
</html>