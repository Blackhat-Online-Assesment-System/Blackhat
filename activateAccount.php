<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File activateAccount.php
* Activate the users account
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(true, null);

/*
* If the URL has a "hash" in it, we activate the account with that Hash
*/
if(!isset($_GET['hash'])){
	//If Missing Hash, redirect to Error page
	header('Location: error.php?error=Incorrect Activation Link&verifymsg=' . CreateAuthenticatedMessageHash());
} else {
	//Otherwise set the account as verifed
	$sql = "UPDATE `Users` SET `AccountVerified` = '1' WHERE `VerifyHash` = '" . SanitizeSQLEntry($_GET['hash']) . "' AND `AccountVerified` = '0'";
	$stm = $DatabaseConnection->prepare($sql);
    $stm->execute();
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Account Activated | <?php echo $SiteTitle; ?></title>
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
			    <h2>Thanks for verifying! Your all setup!</h2>
			    	<br>
			    	<a href="login.php" class="wst-button">Login</a>
			</section>
		</main>
	</body>
</html>