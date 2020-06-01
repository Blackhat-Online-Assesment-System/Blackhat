<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File instructorGrades.php
* Display all grades from assignments
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "instructor");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Grades | <?php echo $SiteTitle; ?></title>
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
				<h1><strong>Grades</strong></h1>
					<br>
					<br>
				<?php
					$Query = "SELECT
								A.`AssignmentID`,
								A.`AssignmentHash`,
								A.`AssignmentName`,
								A.`AssignmentDescription`,
								DATE_FORMAT(A.`DueDate`, '%W, %M %D %Y %h:%i %p') as FormattedDueDate,
								C.`CourseName`,
								C.`CourseID`
							FROM
								`Course` AS C
							    	INNER JOIN
								`Assignment` AS A
							    	ON A.`CourseID` = C.`CourseID`
							    WHERE C.`TeacherID` = " . SanitizeSQLEntry($_SESSION['UserID']) . "
							ORDER BY C.`CourseID` ASC";
					$stm = $DatabaseConnection->prepare($Query);
				    $stm->execute();
				    $records = $stm->fetchAll();
				    $RowCount = $stm->rowCount();
				    $CurrentCourse = null;
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
				    					<strong><?php echo $row['CourseName']; ?></strong>
				    				</h3>
				    					<br>
				    				<table class='align-center'>
					    				<thead>
					    					<th>Assignment Name</th>
					    					<th>Assignment Description</th>
					    					<th>Due Date</th>
					    					<th>Review</th>
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
					    					<th>Review</th>
					    				</thead>
				    			<?php
				    		}
				    		// Then regardless of the course, let's output the assignment info
				    		echo "<tr>
				    				<td>" . $row['AssignmentName'] . "</td>
				    				<td>" . $row['AssignmentDescription'] . "</td>
				    				<td>" . $row['FormattedDueDate'] . "</td>
				    				<td><a href='reviewAssignmentResults.php?ID=" . $row['AssignmentHash'] . "&CID=" . $row['CourseID'] . "'>Review</a></td>";
				    		echo "</tr>"; 
				    		//Finally, set $CurrentCourse to the actual last course name
			    			$CurrentCourse = $row['CourseName'];
				    	}
				    	//And output the ending </table>
				    	echo "</table>";
				    } else {
				    	echo "<p>Yikes! Looks like you don't have any assignments yet, create your first assignment to start collecting grades!</p>";
				    }
				?>
			</section>
		</main>
	</body>
</html>