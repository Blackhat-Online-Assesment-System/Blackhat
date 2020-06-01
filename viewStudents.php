<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/
/**

* @File viewStudents.php
* Display all students in course and their emails
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "instructor");

/*
* Check course is set and a real course
*/
$Course = "SELECT `CourseID`, `CourseName` FROM `Course` WHERE `CourseJoinCode` = '" . SanitizeSQLEntry($_GET['c']) . "'";
$stm = $DatabaseConnection->prepare($Course);
$stm->execute();
$Courses = $stm->fetchAll();
$RowCount = $stm->rowCount();
if(!isset($_GET['c']) || $_GET['c'] == "" || $RowCount == 0){
	header('Location: /error.php?error=Sorry that didn\'t work! Please try again&verifymsg=' . CreateAuthenticatedMessageHash() . '');
	exit();
}
// Check if deleting a user from the course
if($_GET['Action'] == "Remove" && isset($_GET['ID'])){
	$RemoveUser = "DELETE FROM `LinkUserCourse` WHERE `CourseID` = " . $Courses[0]["CourseID"] . " AND `UserID` = " . SanitizeSQLEntry($_GET['ID']);
	$stm = $DatabaseConnection->prepare($RemoveUser);
	$stm->execute();
	$DeletedUser = true;
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Students | <?php echo $SiteTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="assets/css/main.css?version=5">
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
				<h1><strong>Course Name: <?php echo $Courses[0]["CourseName"]; ?></strong></h1>
				<h3><?php if(isset($DeletedUser)) echo "User Removed from Course!"; ?></h3>
					<br>
					<br>
				<?php
					$Query = "SELECT
									U.`UserID`,
									U.`UserFullName`,
									U.`Email`,
									U.`SchoolID`
								FROM
									`LinkUserCourse` AS LUC
								INNER JOIN
									`Users` AS U
										ON U.`UserID` = LUC.`UserID`
								WHERE LUC.`CourseID` = '" . $Courses[0]["CourseID"] . "'";
					$stm = $DatabaseConnection->prepare($Query);
				    $stm->execute();
				    $records = $stm->fetchAll();
				    $RowCount = $stm->rowCount();
				    //Check he has at least one course
				    if($RowCount > 0){
				    	echo "<table class='align-center'>";
				    	echo "<thead><th>Name</th><th>Email</th><th>School ID</th><th>Remove from Course</th></thead>";
					    	foreach ($records as $row) {
					    		echo "<tr><td>" . $row['UserFullName'] . "</td><td><a href='mailto:" . $row['Email'] . "'>" . $row['Email'] . "</a></td><td>" . $row['SchoolID'] . "</td><td><a href='viewStudents.php?Action=Remove&ID=" . $row['UserID'] . "&c=" . $_GET['c'] . "'>Remove</a></td></tr>";
					    	}
				    	echo "</table>";
				    } else {
				    	echo "<p>Looks like this course is empty! Make sure to share the Join Code \"" . $_GET['c'] . "\" to add students! </p>";
				    }
				?>
			</section>
		</main>
	</body>
</html>