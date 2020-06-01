<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File index.php
* Redirect to Assignments
*/

// Include System Functions and Initiate the System!
require_once('functions.php');

if($_SESSION['UserRole'] == "instructor"){
	header('Location: instructorAssignments.php');
} else {
	header('Location: assignments.php');
}

?>