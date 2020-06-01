<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File userSettings.php
* Allow users to change their settings
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, null);

//Update Settings
if(isset($_POST['UserFullName']) && isset($_POST['Email']) && isset($_POST['SchoolID'])){
	//Check email is not used already
	$Query1 = "SELECT * FROM `Users` WHERE `Email` = '" . SanitizeSQLEntry($_POST['Email']) . "' AND `UserID` NOT LIKE " . $_SESSION['UserID'];
	$stm = $DatabaseConnection->prepare($Query2);
    $stm->execute();
    $records = $stm->fetchAll();
    $row_count = $stm->rowCount();
    if($row_count == 0){
    	//Email not used, update
    	$Query2 = "UPDATE `Users` SET `UserFullName` = '" . SanitizeSQLEntry($_POST['UserFullName']) . "', `Email` = '" . SanitizeSQLEntry($_POST['Email']) . "', `SchoolID` = '" . SanitizeSQLEntry($_POST['SchoolID']) . "' WHERE `UserID` = " . SanitizeSQLEntry($_SESSION['UserID']);
    	$stm = $DatabaseConnection->prepare($Query2);
    	$stm->execute();
    	$UserMessage = "Update Settings Success!";
    } else {
    	$UserMessage = "Error, email address already in use!";
    }
} else if(isset($_POST['CurrentPassword']) && isset($_POST['NewPassword']) && isset($_POST['ConfirmPassword'])){
	if($_POST['NewPassword'] != $_POST['ConfirmPassword']){
		$UserMessage = "Error password mismatch";
	} else {
		// Validate Current Password
		if(password_verify($_POST['CurrentPassword'], GetLoggedInUserInfo()["Password"])){
			$UpdatePW = "UPDATE `Users` SET `Password` = '" . password_hash(SanitizeSQLEntry($_POST['NewPassword']), PASSWORD_DEFAULT) . "' WHERE `UserID` = " . SanitizeSQLEntry($_SESSION['UserID']);
			$stm = $DatabaseConnection->prepare($UpdatePW);
	    	$stm->execute();
	    	$UserMessage = "Password Reset Success!";
		} else {
			$UserMessage = "Error current password incorrect!";
		}
	}
}

$LoggedInUserRecords = GetLoggedInUserInfo();

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>User Preferences | <?php echo $SiteTitle; ?></title>
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
				<h1><strong>User Preferences</strong></h1>
				<h3><?php echo $UserMessage; ?></h3>
					<br>
					<a href="twoFactorAuth.php"><button class="wst-button">2 Factor Authentication Settings</button></a>
					<br><br>
				<h3><strong>User Details</strong></h3>
				<form action="userSettings.php" method="POST" class="wst-login-form">
					Name: <input type="text" name="UserFullName" value="<?php echo $LoggedInUserRecords["UserFullName"];?>">
						<br>
					Email: <input type="email" name="Email" value="<?php echo $LoggedInUserRecords["Email"];?>">
						<br>
					School ID: <input type="text" name="SchoolID" value="<?php echo $LoggedInUserRecords["SchoolID"];?>">
						<p>This is used by your instructor to properly identify you, <strong>do NOT enter your Social Security number in this box!</strong></p>
					<input type="submit" name="submit" value="Update Settings">
				</form>
					<br><br>
				<h3><strong>Change Password</strong></h3>
				<form action="userSettings.php" method="POST" class="wst-login-form">
					Current Password: <input type="password" name="CurrentPassword" value="">
						<br>
					New Password: <input type="password" name="NewPassword" value="">
						<br>
					Confirm Password: <input type="password" name="ConfirmPassword" value="">
					<input type="submit" name="submit" value="Change">
				</form>
			</section>
		</main>
	</body>
</html>