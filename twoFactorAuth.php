<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File twoFactorAuth.php
* Allow users to enable/disable 2 Factor Authentication
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, null);
require_once 'PHPGangsta/GoogleAuthenticator.php';

//Enable and Disable
if(isset($_POST['Enable2FA']) && $_POST['Enable2FA'] == "Yes"){
	// Create the Authentication
	$GA = new PHPGangsta_GoogleAuthenticator();
	$Secret = $GA->createSecret();
	$qrCodeUrl = $GA->getQRCodeGoogleUrl('BlackHat', $Secret);
	// Now update the account
	$Update = "UPDATE `Users` SET `TwoFactorEnabled` = '1', `TFASecret` = '" . $Secret . "' WHERE `UserID` = '" . $_SESSION['UserID'] . "'";
	$stm = $DatabaseConnection->prepare($Update);
	$stm->execute();
	$IsTwoFactorEnabled = true;
	$Message = "Two Factor Authentication has been Enabled! Scan the QR Code into your Google Authenticator app, on your next login you will need to get the code from there to login.";
} else if(isset($_POST['Enable2FA']) && $_POST['Enable2FA'] == "No"){
	// Now update the account
	$Update = "UPDATE `Users` SET `TwoFactorEnabled` = '0', `TFASecret` = 'NULL' WHERE `UserID` = '" . $_SESSION['UserID'] . "'";
	$stm = $DatabaseConnection->prepare($Update);
	$stm->execute();
	$IsTwoFactorEnabled = false;
	$Message = "Two Factor Authentication has been Disabled!";
}
$LoggedInUserRecords = GetLoggedInUserInfo();
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Two Factor Authentication | <?php echo $SiteTitle; ?></title>
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
				    // POST the Top Menu Bar
				    require_once('views/menu.php');
				?>
			</section>
		</header>
		<main>
			<section class="full-width-content wst-main-content align-center">
				<h1><strong>Two Factor Authentication</strong></h1>
				<h3><?php echo $Message; ?></h3>
					<br>
					<?php
						if($IsTwoFactorEnabled){
							echo "QR Code for Google Authenticator: <br> <img src='" . $qrCodeUrl . "'/>";
						}
					?>
				<form action="twoFactorAuth.php" method="POST">
					<?php
						if($LoggedInUserRecords["TwoFactorEnabled"] != '1'){
					?>
						<p>Two Factor Authentication is Disabled for your account</p>
							<input type="hidden" name="Enable2FA" value="Yes">
						<input type="submit" value="Enable">
					<?php
						} else {
					?>
						<p>Two Factor Authentication is Enabled for your account</p>
							<input type="hidden" name="Enable2FA" value="No">
						<input type="submit" value="Disable">
					<?php
						}
					?>
				</form>
					<br><br><br><br>
				<p>Download the Google Authenticator App Today!</p>
				<a href="https://apps.apple.com/us/app/google-authenticator/id388497605" target="_blank">
					<img src="assets/images/iphone.jpg" height="70px" width="200px" />
				</a>
				<a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en_US" target="_blank">
					<img src="assets/images/android.jpg" height="70px" width="200px" />
				</a>
			</section>
		</main>
	</body>
</html>