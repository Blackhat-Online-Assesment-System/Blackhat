<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File courses.php
* Display all courses user is enrolled in
* Allow Users to enroll in other courses as well
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "student");

/*
* If the user added a new course, check the course code is a real code and the user hasn't already added it to their account yet
*/
if(isset($_POST['JoinCode'])) {
	$Query1 = "SELECT * FROM `Course` WHERE `CourseJoinCode` = '" . SanitizeSQLEntry($_POST['JoinCode']) . "'";
	$stm = $DatabaseConnection->prepare($Query1);
    $stm->execute();
    $records = $stm->fetchAll();
    $RowCount = $stm->rowCount();
    if($RowCount > 0){
    	// Course code is OK
    	// Check Logged in User doesn't have the course already added

    	$Query2 = "SELECT * FROM `LinkUserCourse` WHERE `UserID` = '" . $_SESSION['UserID'] . "' AND `CourseID` = '" . $records[0]['CourseID'] . "'";
    	$stm = $DatabaseConnection->prepare($Query2);
	    $stm->execute();
	    $RowCount = $stm->rowCount();
	    if($RowCount == 0){
	    	// Finally we can add to his accout

	    	$Query3 = "INSERT INTO `LinkUserCourse` (UserID, UserRole, CourseID, Status) VALUES ('" . $_SESSION['UserID'] . "', '" . $_SESSION['UserRole'] . "', '" . $records[0]['CourseID'] . "', '1')";
	    	$stm = $DatabaseConnection->prepare($Query3);
		    $stm->execute();
		    $Message = "Class added successfully!";
	    } else {
	    	$Message = "Error, you are already subscribed to this class!";
	    }

    } else {
    	$Message = "Error Join Code invalid!";
    }
}

/*
* If user is leaving a course 
*/
if(isset($_GET['Action'])){
	// Remove them from the Course
	$Leave = "DELETE FROM `LinkUserCourse` WHERE `UserID` = " . $_SESSION['UserID'] . " AND `CourseID` = " . SanitizeSQLEntry($_GET['CID']) . "";
	$stm = $DatabaseConnection->prepare($Leave);
    $stm->execute();

    // Remove previous Assignments
    $Assignments = "SELECT `AssignmentID` FROM `Assignment` WHERE `CourseID` = " . SanitizeSQLEntry($_GET['CID']);
	$stm = $DatabaseConnection->prepare($Assignments);
    $stm->execute();
    $Assignments = $stm->fetchAll();
    foreach ($Assignments as $A) {
    	$Leave = "DELETE FROM `LinkUserAssignment` WHERE `UserID` = " . $_SESSION['UserID'] . " AND `AssignmentID` = " . $A['AssignmentID'] . "";
		$stm = $DatabaseConnection->prepare($Leave);
	    $stm->execute();
    }

    $Message = "Class left successfully!";
}

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Courses | <?php echo $SiteTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="assets/css/main.css?version=53">
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
				<h1><strong>My Courses</strong></h1>
				<h3><?php if(isset($Message)) echo $Message; ?></h3>
					<br>
					<br>
				<?php
				//Get All Users Courses
					$Query = "SELECT 
								C.`CourseID` AS SectionNumber,
								U.`UserFullName` AS TeacherName,
								C.`CourseName`,
								C.`CourseDescription`,
								C.`ClassTime`
							FROM `LinkUserCourse` AS L 
								INNER JOIN 
							`Course` AS C 
								ON L.`CourseID` = C.`CourseID`
								INNER JOIN
							`Users` AS U
								ON C.`TeacherID` = U.`UserID`
						WHERE L.`UserID` = " . $_SESSION['UserID'];
					$stm = $DatabaseConnection->prepare($Query);
				    $stm->execute();
				    $records = $stm->fetchAll();
				    $RowCount = $stm->rowCount();

				    //Check he has at least one course
				    if($RowCount > 0){
				    	echo "<table class='align-center'>";
				    	echo "<thead><th>Class Name</th><th>Class Description</th><th>Class Time</th><th>Course ID</th><th>Instructor Name</th><th>Leave Course</th></thead>";
					    	
					    	foreach ($records as $row) {
					    		echo "<tr><td>" . $row['CourseName'] . "</td><td>" . $row['CourseDescription'] . "</td><td>" . $row['ClassTime'] . "</td><td>" . $row['SectionNumber'] . "</td><td>" . $row['TeacherName'] . "</td><td><a href='courses.php?Action=Leave&CID=" . $row['SectionNumber'] . "'>Leave</a></td></tr>";
					    	}

				    	echo "</table>";
				    } else {
				    	echo "<p>Yikes! Looks like you don't have any assigned courses yet, add a course with the form below!</p>";
				    }
				?>
					<br>
					<hr>
					<h3><strong>Add Course</strong></h3>
					<br><br>
				<form action="courses.php" method="POST" class="align-center wst-form-half-width">
					Course Join Code: <input type="text" name="JoinCode">
						<br>
					<input type="submit" name="submit" value="Join">
				</form>
			</section>
		</main>
	</body>
</html>