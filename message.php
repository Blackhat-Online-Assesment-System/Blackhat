<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File message.php
* Shows the user messages, that's it!
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(true, null);

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $SiteTitle; ?></title>
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
			    <?php
			    //Check we have a message and the message hash is correct
			    	if(isset($_GET['Message']) && VerifyMessageAuthenticity($_GET['MessageVerify'])){
				    	switch ($_GET['Message']) {
				    		case 'AccountCreated':
				    			echo "Account Created Successfully! <br> An activation email has been sent to your email address, please confirm your email and then <a href=login.php>click here to login</a><br><p>(Verification Email may take a few minutes to arrive)</p>";
				    			break;
				    		case 'SubmitAssignmentSuccess':
				    			echo "Assignment submitted Successfully!";
				    			break;
				    		case 'PasswordResetOK':
				    			echo "Password Reset Success! <a href=login.php>Click here to login!</a>";
				    			break;
				    	}
				    } else {
				    	header('Location: index.php');
				    }
			    ?>
			</section>
		</main>
	</body>
</html>