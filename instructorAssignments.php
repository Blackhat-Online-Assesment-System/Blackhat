<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File instructorAssignments.php
* Instructor Assignments
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "instructor");

/*
* Allow the Instructor to add a new Assignment
*/
if(isset($_POST['AssignmentName'])) {
	// We want to add a new course
	$AssignmentHash = strtoupper(substr(GenerateRandomHash(), 0, 6));
	$Query1 = "INSERT INTO `Assignment` (AssignmentHash, CourseID, AssignmentName, AssignmentDescription, DueDate) VALUES ('" . $AssignmentHash . "', '" . SanitizeSQLEntry($_POST['CourseID']) . "', '" . SanitizeSQLEntry($_POST['AssignmentName']) . "', '" . SanitizeSQLEntry($_POST['AssignmentDescription']) . "', '" . SanitizeSQLEntry($_POST['DueDate']) . "')";
	$stm = $DatabaseConnection->prepare($Query1);
    $stm->execute();
	header('Location: editAssignment.php?ID=' .  $AssignmentHash);
}

//Delete Assignment
if($_GET['Action'] == "Remove" && isset($_GET['ID'])){
	$RemoveCourses = "DELETE FROM `Assignment` WHERE `AssignmentHash` = '" . SanitizeSQLEntry($_GET['ID']) . "'";
	$stm = $DatabaseConnection->prepare($RemoveCourses);
	$stm->execute();
	$DeletedUser = true;
}

// Get Teachers Courses:
$TeacherCoursesQuery = "SELECT 
		C.`CourseID`,
		C.`CourseName`
	FROM
		`Course` AS C 
	WHERE C.`TeacherID` = " . $_SESSION['UserID'];					
	$stm = $DatabaseConnection->prepare($TeacherCoursesQuery);
$stm->execute();
$TeacherCourses = $stm->fetchAll();
$TeacherCoursesRowCount = $stm->rowCount();
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Assignments | <?php echo $SiteTitle; ?></title>
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
				<h1><strong>Assignments</strong></h1>
					<br>
					<br>
				<?php
				// Get all Assignments for the user
					$Query = "SELECT
								C.`CourseID`,
								C.`CourseName`,
								A.`AssignmentID`,
								A.`AssignmentHash`,
								DATE_FORMAT(A.`DueDate`, '%W, %M %D %Y %h:%i %p') as FormattedDueDate,
								A.`AssignmentID`,
								IF(A.`OpenDate` != 'NULL', DATE_FORMAT(A.`OpenDate`, '%W, %M %D %Y %h:%i %p'), 'Not Scheduled') as `FormattedOpenDate`,
								A.`AssignmentName`,
								A.`AssignmentDescription`,
								IF(`DueDate` > NOW(), 'Open', 'Closed') AS `AssignmentOpen`
							FROM `Course` AS C
								INNER JOIN
							`Assignment` AS A
								ON A.`CourseID` = C.`CourseID`
						WHERE C.`TeacherID` = " . $_SESSION['UserID'] . "
						ORDER BY C.`CourseID` DESC, A.`DueDate` DESC";
					$stm = $DatabaseConnection->prepare($Query);
				    $stm->execute();
				    $records = $stm->fetchAll();
				    $RowCount = $stm->rowCount();
				    // Add flag for CurrentCourse to know how to divide them up
				    $CurrentCourse = null;
				    // Check We have assignments at all :)
				    if($RowCount > 0){
				    	//Then Loop the Assignments
				    	foreach ($records as $row) {
				    		//Output the starting <table>
				    			// I.E. Base Case
				    		if($CurrentCourse == null) {
				    			?>
				    				<h3>
				    					<strong><?php echo $row['CourseName'] . " - " . $row['CourseID']; ?></strong>
				    				</h3>
				    					<br>
				    				<table class='align-center'>
					    				<thead>
					    					<th>Assignment Name</th>
					    					<th>Assignment Description</th>
					    					<th>Due Date</th>
					    					<th>Open Date</th>
					    					<th>Assignment Status</th>
					    					<th>Edit Assignment</th>
					    					<th>Delete Assignment</th>
					    				</thead>
				    				<?php
				    		}
				    		//If the course name is diffrent than the one before it, we split it into another table
				    			// Each Successive Case
				    		if(($CurrentCourse != $row['CourseName']) && ($CurrentCourse != null)){
				    			?>
				    				</table>
				    					<br>
				    					<br>
				    				<h3>
				    					<strong><?php echo $row['CourseName']; ?></strong>
				    				</h3>
				    					<br>
			    					<table class='align-center'>
					    				<thead>
					    					<th>Assignment Name</th>
					    					<th>Assignment Description</th>
					    					<th>Due Date</th>
					    					<th>Open Date</th>
					    					<th>Assignment Status</th>
					    					<th>Edit Assignment</th>
					    					<th>Delete Assignment</th>
					    				</thead>
				    			<?php
				    		}
				    		// Then regardless of the course, let's output the assignment info
				    		echo "<tr>
				    				<td>" . $row['AssignmentName'] . "</td>
				    				<td>" . $row['AssignmentDescription'] . "</td>
				    				<td>" . $row['FormattedDueDate'] . "</td>
				    				<td>" . $row['FormattedOpenDate'] . "</td>";
						    			if($row['AssignmentOpen'] == "Open" && $row['FormattedOpenDate'] != 'Not Scheduled'){
						    				echo "<td>Open</td>";
						    			} else {
						    				echo "<td>Closed</td>";
						    			}
						    		echo "<td><a href='editAssignment.php?ID=" . $row['AssignmentHash'] . "'>Edit</td>";
						    		echo "<td><a href='instructorAssignments.php?Action=Remove&ID=" . $row['AssignmentHash'] . "'>Delete</td>";
				    		echo "</tr>"; 
				    		//Finally, set $CurrentCourse to the actual last course name
			    			$CurrentCourse = $row['CourseName'];
				    	}
				    	//And output the ending </table>
				    	echo "</table>";
				    } else {
				    	if($TeacherCoursesRowCount == 0){
				    		echo "Oops - you have no courses made! Please make a course first to add assignments!";
				    	} else {
				    		echo "<p>No Assignments created, use the form below to create an assignment!</p>";
				    	}
				    }
				?>
					<br>
					<hr>
					<br>
				<?php 
					if($TeacherCoursesRowCount != 0){
				?>
				<form action="instructorAssignments.php" method="POST" class="align-center wst-form-half-width">
					<h3><strong>Create Assignment</strong></h3>
						<br><br>
					Course:
						<select name="CourseID" required="required">
						<?php
							foreach ($TeacherCourses as $TeacherCourse) {
								echo "<option value='" . $TeacherCourse['CourseID'] . "'>" . $TeacherCourse['CourseName'] . " - " . $TeacherCourse['CourseID'] . "</option>";
							}
						?>
					</select><br>
					Assignment Name: <input type="text" name="AssignmentName" required="required">
						<br>
					Assignment Description: <input type="text" name="AssignmentDescription" required="required">
						<br>
					Due Date: <input type="datetime-local" name="DueDate" required="required" value="<?php echo date_format(date_create(), "Y-m-d") . "T" . date_format(date_create(), "H:i"); ?>">
						<br><i>(Format YYYY-MM-DD HH:MI:SS)</i><br>
					<input type="submit" name="submit" value="Create">
				</form>
			<?php }?>
			</section>
		</main>
	</body>
</html>