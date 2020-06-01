<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File error.php
* Display any errors to client on this page
* Errors may come from submissions of forms, system startup or otherwise
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(true, null);

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Error | <?php echo $SiteTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="assets/css/main.css?version=2">
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
				    require_once('views/menu.php');
				?>
			</section>
		</header>
		<main>
			<section class="full-width-content wst-main-content align-center">
				<?php
				    //Check we have an error in the URL bar, and the message isn't blank
				    if(isset($_GET['error']) && isset($_GET['verifymsg']) && VerifyMessageAuthenticity($_GET['verifymsg'])){
				        echo "<h1 class='wst-error-message'>Error: " . $_GET['error'] . "</h2>";
				        echo "<p><a href='javascript:history.go(-1)' title='Return to the previous page'>&laquo; Click here to try again</a></p>";
				        echo "<p>If the issue persists, please contact your instructor or IT Department.</p>";
				    } else {
				    // Otherwise go to index.php
				        header('Location: index.php');
				    }
				    
				?>
			</section>
		</main>
	</body>
</html>