<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File grades.php
* Display all grades from assignments
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "student");

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
				<h1><strong>My Grades</strong></h1>
					<br>
					<h3>
						<!-- Added due to akward offset between the Database and Server time, if you are on a syncronized server, remove this -->
						Please note: Grades may take up to 3 hours after closing to be processed and graded.
					</h3>
					<br>
				<?php
					$Query = "SELECT
								A.`AssignmentID`,
								A.`AssignmentHash`,
								CASE WHEN LUA.`QuestionsCorrect` IS NULL THEN 0 ELSE LUA.`QuestionsCorrect` END AS QuestionsCorrect,
								IF(`DueDate` > NOW(), 'Open', 'Closed') AS `AssignmentOpen`,
								LUA.`OverallGrade`,
								A.`AssignmentName`,
								A.`AssignmentDescription`,
								DATE_FORMAT(A.`DueDate`, '%W, %M %D %Y %h:%i %p') as FormattedDueDate,
								C.`CourseName`
							FROM
								`LinkUserCourse` AS LUC
									INNER JOIN
								`Course` AS C
									ON LUC.`CourseID` = C.`CourseID`
									INNER JOIN
							    `Assignment` AS A
							    	ON LUC.`CourseID` = A.`CourseID`
							        LEFT JOIN
							    `LinkUserAssignment` AS LUA
							    	ON A.`AssignmentID` = LUA.`AssignmentID`
							WHERE LUC.`UserID` = " . SanitizeSQLEntry($_SESSION['UserID']) . "
							ORDER BY A.`CourseID`, A.`DueDate` DESC";
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
					    					<th>Date</th>
					    					<th>Questions Correct</th>
					    					<th>Overall Score</th>
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
					    					<th>Date</th>
					    					<th>Questions Correct</th>
					    					<th>Overall Score</th>
					    					<th>Review</th>
					    				</thead>
				    			<?php
				    		}


				    		// Then regardless of the course, let's output the assignment info
				    		echo "<tr>
				    				<td>" . $row['AssignmentName'] . "</td>
				    				<td>" . $row['AssignmentDescription'] . "</td>
				    				<td>" . $row['FormattedDueDate'] . "</td>";
					    		if($row['AssignmentOpen'] == "Closed"){
					    			echo "<td>" . $row['QuestionsCorrect'] . "</td>
					    				<td>" . number_format((float)$row['OverallGrade']*100, 2, '.', '') . "</td>
					    				<td><a href='reviewAssignment.php?id=" . $row['AssignmentHash'] . "'>Review</a></td>";
					    		} else {
					    			echo "<td>Available after due date</td>
					    				<td>Available after due date</td>
					    				<td>Available after due date</td>";
					    		}
				    				
				    		echo "</tr>"; 

				    		//Finally, set $CurrentCourse to the actual last course name
			    			$CurrentCourse = $row['CourseName'];
				    	}
				    	//And output the ending </table>
				    	echo "</table>";
				    } else {
				    	echo "<p>No grades found!</p>";
				    }
				?>
			</section>
		</main>
	</body>
</html>