<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/
/**
* @File instructorCourses.php
* Display all courses user is the instructor of, allow creation of new course
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "instructor");

/*
* Allow the Instructor to add a new course
*/
if(isset($_POST['CourseName'])) {
	// We want to add a new course
	$Query1 = "INSERT INTO `Course` (TeacherID, CourseName, CourseDescription, ClassTime, CourseJoinCode) VALUES ('" . $_SESSION['UserID'] . "', '" . SanitizeSQLEntry($_POST['CourseName']) . "', '" . SanitizeSQLEntry($_POST['CourseDescription']) . "', '" . SanitizeSQLEntry($_POST['CourseTime']) . "', '" . strtoupper(substr(GenerateRandomHash(), 0, 6)) . "')";
	$stm = $DatabaseConnection->prepare($Query1);
    $stm->execute();
	$Message = "Class created successfully!";
}

// Delete Course
if($_GET['Action'] == "Remove" && isset($_GET['ID'])){
	$RemoveCourses = "DELETE FROM `Course` WHERE `CourseID` = '" . SanitizeSQLEntry($_GET['ID']) . "'";
	$stm = $DatabaseConnection->prepare($RemoveCourses);
	$stm->execute();
	$DeletedUser = true;
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Courses | <?php echo $SiteTitle; ?></title>
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
				<h1><strong>My Courses</strong></h1>
				<h3><?php if(isset($Message)) echo $Message; ?></h3>
					<br>
					<br>
				<?php
				//Get All Users Courses
					$Query = "SELECT 
								C.`CourseID`,
								C.`CourseName`,
								C.`CourseDescription`,
								C.`ClassTime`,
								C.`CourseJoinCode`,
								(
									SELECT COUNT(*)
									FROM `LinkUserCourse`
									WHERE `CourseID` = C.`CourseID`
								) AS NumberOfStudents
							FROM
								`Course` AS C 
						WHERE C.`TeacherID` = " . $_SESSION['UserID'];					
					$stm = $DatabaseConnection->prepare($Query);
				    $stm->execute();
				    $records = $stm->fetchAll();
				    $RowCount = $stm->rowCount();
				    //Check he has at least one course
				    if($RowCount > 0){
				    	echo "<table class='align-center table table-bordered table-dark'>";
				    	echo "<thead><th>Course Name</th><th>Course Description</th><th>Course Time</th><th>Course ID</th><th>Course Join Code</th><th>Number of Students</th><th>Delete Course</th></thead>";

					    	foreach ($records as $row) {
					    		echo "<tr><td>" . $row['CourseName'] . "</td><td>" . $row['CourseDescription'] . "</td><td>" . $row['ClassTime'] . "</td><td>" . $row['CourseID'] . "</td><td>" . $row['CourseJoinCode'] . "</td><td><a href='viewStudents.php?c=" . $row['CourseJoinCode'] . "' class='btn btn-secondary btn-lg active'>" . $row['NumberOfStudents'] . "</a></td><td><a href='instructorCourses.php?Action=Remove&ID=" . $row['CourseID'] . "&c=" . $row['CourseJoinCode'] . "' class='btn btn-secondary btn-lg active'>Delete</a></td></tr>";
					    	}
				    	echo "</table>";
				    } else {
				    	echo "<p>Yikes! Looks like you don't have any courses made yet, use the form below to make your first course!</p>";
				    }
				?>
					<br>
					<hr>
					<br>
				<form action="instructorCourses.php" method="POST" class="align-center wst-form-half-width">
					<h3><strong>Create Course</strong></h3>
						<br>
					Course Name:<br> <input type="text" name="CourseName" required="required">
						<br>
					Course Description:<br> <input type="text" name="CourseDescription">
						<br>
					Course Date and Time (EX: M/W 10:45-12:00PM):<br> <input type="text" name="CourseTime">
						<br>
					<input type="submit" name="submit" value="Create">
				</form>
			</section>
		</main>
	</body>
</html>