<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* Required Functions File
* This file stores required functions and connection scripts that is needed to run the site
* In general, you shouldn't need to edit most of this file, save for the specifically marked portions
*/

/*=========================[Edit Information below this line]=========================*/

/* 1. Set the Website Title */
	$SiteTitle = "Blackhat";

/* 2. MySQL Connection Settings */
	DEFINE( 'DB_HOST', '' );					// HostName
	DEFINE( 'DB_NAME', '' );					// Database Name
	DEFINE( 'DB_USER', '' );					// Database Username
	DEFINE( 'DB_PASSWORD', '' );				// Database Password


/* 3. Set TimeZone */
	date_default_timezone_set("America/New_York");

/* 4. Email Settings */
	$SMTP_Debug = 0;
	$SMTP_Host = "";
	$SMTP_Auth = true;
	$SMTP_Username = "";
	$SMTP_Password = "";
	$SMTP_Security = "";
	$SMTP_Port = 25;
	$SMTP_SendEmailsFromAddress = "";


/*=======================[That's it! Stop Editing from here on!]=======================*/

/* Hash for GET Authentications */
	// To verify internal GET requests, we append an "auth" parameter to ensure it was really coming from us

	//Set the random master Hash
	DEFINE( 'RAND_AUTH_HASH', 'XW0oHxRrh2tL45x5XfFBbJA5DbrvVSp7EIxDnhCaFGPNETFbgmINla9hNOYYLVIl');

	// Now each time we need to send a redirect, we make a new random hash of that master password
	function CreateAuthenticatedMessageHash(){
		return password_hash(RAND_AUTH_HASH, PASSWORD_DEFAULT);
	}

	// Then to verify, we check the hash matches
	function VerifyMessageAuthenticity($ReceivedHash){
		return password_verify(RAND_AUTH_HASH, $ReceivedHash);
	}

/*
* Start Sessions for User Logins
*/
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/*
* @Function TestMySQLConnection()
* @Variable $DatabaseConnection a variable to store the database connection assuming it can connect
* @Params None
* @Return (Boolean) true or false if MySQL Connection is ok or not
* Function to test if we can connect to the databasing server, if not, we should throw an error
*/
$DatabaseConnection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . "", "" . DB_USER . "", "" . DB_PASSWORD . "");
function TestMySQLConnection() {
	try {
		$DatabaseConnection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . "", "" . DB_USER . "", "" . DB_PASSWORD . "");
		$DatabaseConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return true;
	} catch(Exception $e) {
		return false;
	}
}

/*
* @Function SanitizeSQLEntry()
* @Params $Data to insert into Database (Likely user input)
* @Return $Data that is safe to insert
* Safe input to MySQL
* Keep safe from SQL Injections and Other Attacks (And accidental commas)
*/
function SanitizeSQLEntry($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = addslashes($data);
  return $data;
}

/*
* @Function GetLoggedInUserInfo()
* @Params None
* @Return (Array) of Logged in User Info
* Get the Logged in User's info
*/
function GetLoggedInUserInfo(){
	$Query = "SELECT * FROM `Users` WHERE `UserID` = " . $_SESSION['UserID'];
	$stm = $GLOBALS['DatabaseConnection']->prepare($Query);
	$stm->execute();
	return $stm->fetchAll()[0];
}

/*
* @Function CheckUserAuthentication()
* @Params None
* @Return (Boolean) if user is logged in already or not
* Check User if User is logged in
*/
function CheckUserAuthentication(){
	if(isset($_SESSION['IsLoggedIn']) && $_SESSION['IsLoggedIn'] && $_SESSION['UserID'] != null && $_SESSION['UserRole'] != null && $_SESSION['UserEmail'] != null){
		return true;
	} else {
		return false;
	}
}

/*
* @Function GetLoggedInUserInfo()
* @Params None
* @Return (String) of 32 length Alphanumeric
* Return a random alphanumeric string to be used for various ID's and hashing
*/
function GenerateRandomHash(){
	return bin2hex(random_bytes(32));
}

/*
* @Function GetLoggedInUserInfo()
* @Param $UserSecret, the Secret code for the User
* @Param $UserCode, the code from Google Authenticator 
* @Return (Boolean) if verfied or not
* This functyions Verify's Google 2FA for logins
*/
function Verify2FA($UserSecret, $UserCode){
	$GA = new PHPGangsta_GoogleAuthenticator();

	$checkResult = $GA->verifyCode($UserSecret, $UserCode, 1);    // 2 = 2*30sec clock tolerance
	if ($checkResult) {
	    return true;
	} else {
	    return false;
	}
}

/*=======================[Startup Functions]=======================*/
/* 
* This function, on each page load, loads the required functions and files
* If any errors are caught at this point, we redirect to an error page
* @Param DoNotRedirect -Setup local flag, if this is set as true on the individual page, we don't redirect anwhere, 
* but allow certain errors to be left as is, I.E. - Do not redirect to login, if we're on the login page 
* @Param $PageUserRole gets page role for that page, if the $_SESSION['UserRole'] does not equal the $PageUserRole, we give a 403 Denied Error
*/

function InitiateSystem($DoNotRedirect, $PageUserRole){

	$ErrorMessage = "";
	$IsError = false;

	/* 1. Check our Database connection */
	if(!TestMySQLConnection()){
		$ErrorMessage = "Unable to connect to the database! Please check your connection settings and try again!";
		$IsError = true;
	}

	/* 2. Check Authentication */
	if(!CheckUserAuthentication() && (!$DoNotRedirect)){
		header('Location: login.php');
		die();
	}

	/* 3. Check User Role is on proper page */
	if(($PageUserRole == "student" && $_SESSION['UserRole'] == "instructor") || ($PageUserRole == "instructor" && $_SESSION['UserRole'] == "student")){
		$ErrorMessage = "403 - Access Denied";
		$IsError = true;
	}
    
    // Now, if an error was found, redirect to error page
    // Check first that we aren't looping back, if an error message is already in the URL, don't redirect again
    if($IsError && (!isset($_GET['error']))){
        header('Location: /error.php?error=' . $ErrorMessage . '&verifymsg=' . CreateAuthenticatedMessageHash() . '');
	    exit();    
    }
    
}
?>