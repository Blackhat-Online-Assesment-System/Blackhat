<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File login.php
* Allows users to login and authenticates their login requests
*/

// Include System Functions and Initiate the System
require_once('functions.php');
InitiateSystem(true, null);

// If the user has submitted the login form, we should authenticate, or throw an error
if(isset($_POST['Email']) && isset($_POST['Password'])){

    //Check Database to see if a user with that email exists, that the account is verified
    $Query = "SELECT * FROM `Users` WHERE `Email` = '" . SanitizeSQLEntry($_POST['Email']) . "' AND `AccountVerified` = '1' LIMIT 1";
    $stm = $DatabaseConnection->prepare($Query);
    $stm->execute();
    $User = $stm->fetchAll();
    $RowCount = $stm->rowCount();
    
    // Now check that the password matches
    if($RowCount > 0 && password_verify(SanitizeSQLEntry($_POST['Password']), $User[0]["Password"])){

    	// Login Good!

    	// Set Sessions
		$_SESSION['UserID'] = $User[0]["UserID"];
        $_SESSION['UserRole'] = $User[0]["UserRoles"];
        $_SESSION['UserEmail'] = $User[0]["Email"];    	

    	// Now if Two Factor Authentication is enabled, we redirect to verify that first

    		if($User[0]["TwoFactorEnabled"] == '1'){
    			$_SESSION['2FAEnabled'] = true;
    			$_SESSION['Pre2FAPassed'] = true;
    			$_SESSION['Post2FAPassed'] = false;
				$_SESSION['IsLoggedIn'] = false;

    				header('Location: verifyTwoFactorAuth.php?Action=Login');
    				exit();

    		} else {
    			
    			// Otherwise, mark as Logged in and redirect to homepage
    			
    			$_SESSION['2FAEnabled'] = false;
    			$_SESSION['Pre2FAPassed'] = null;
    			$_SESSION['Post2FAPassed'] = null;
    			$_SESSION['IsLoggedIn'] = true;
    		}

        header('Location: index.php');
    } else {
    	
    	// Bad Login, Show an Error Message.

    	$_SESSION['IsLoggedIn'] = false;
        $_SESSION['UserID'] = null;
        $_SESSION['UserRole'] = null;
        $_SESSION['UserEmail'] = null;
    	$_SESSION['2FAEnabled'] = null;
		$_SESSION['Pre2FAPassed'] = null;
		$_SESSION['Post2FAPassed'] = null;
        $ShowError = true;
    }
}

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Login | <?php echo $SiteTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="assets/css/main.css?version=32">
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
			    <form action="login.php" method="POST" class="wst-login-form">
			        <h1 class="wst-header-title">Please Login!</h1>
			        <h3 class="wst-error-notice"><?php if(isset($ShowError) && $ShowError) { echo "Error, Invalid Email or Password!"; } ?></h3>
			            <br>
			        Email Address: <input type="email" name="Email" placeholder="Email Address" required="required"> 
			            <br>
			        Password: <input type="password" name="Password" placeholder="Password" required="required">
			            <br>
			        <input type="submit" name="submit" value="Login">
			        	<br>
			        	<br>
			        <a href="requestPasswordReset.php">Having trouble? Reset Password</a>
			    </form>
			    	<br>
			    	<br>
			    <h3>Need an account? Signup today!</h3>
			    	<br>
			    	<a href="signup.php" class="wst-button">Signup</a>
			</section>
		</main>
	</body>
</html>