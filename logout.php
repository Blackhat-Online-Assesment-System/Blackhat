<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File logout.php
* Allows users to logout of the system
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(true, null);

// Kill all the sessions.
$_SESSION['IsLoggedIn'] = false;
$_SESSION['UserID'] = null;
$_SESSION['UserRole'] = null;
$_SESSION['UserEmail'] = null;
$_SESSION['2FAEnabled'] = null;
$_SESSION['Pre2FAPassed'] = null;
$_SESSION['Post2FAPassed'] = null;
session_destroy();

// Redirect to the login page
header('Location: login.php?logout=true&verifymsg=' . CreateAuthenticatedMessageHash());
?>