<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File assignment.php
* Display all completed and incompleted assignments connected to the users account
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "student");

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Assignments | <?php echo $SiteTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="assets/css/main.css?version=51">
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
				<h1><strong>My Assignments</strong></h1>
					<br>
					<br>
				<?php
				// Get all Assignments for the user
					$Query = "SELECT
								L.`CourseID`,
								C.`CourseName`,
								A.`AssignmentID`,
								A.`AssignmentHash`,
								DATE_FORMAT(A.`DueDate`, '%W, %M %D %Y %h:%i %p') as FormattedDueDate,
								A.`AssignmentName`,
								A.`AssignmentDescription`,
								IF(`DueDate` > NOW(), 'Open', 'Closed') AS `AssignmentOpen`
							FROM `LinkUserCourse` AS L 
								INNER JOIN
							`Assignment` AS A
								ON L.`CourseID` = A.`CourseID`
								INNER JOIN
							`Course` AS C
								ON L.`CourseID` = C.`CourseID`
						WHERE L.`UserID` = " . SanitizeSQLEntry($_SESSION['UserID']) . "
							AND A.`OpenDate` < NOW()
						ORDER BY L.`CourseID` DESC, A.`DueDate` DESC";
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
				    					<strong><?php echo $row['CourseName']; ?></strong>
				    				</h3>
				    					<br>
				    				<table class='align-center'>
					    				<thead>
					    					<th>Assignment Name</th>
					    					<th>Assignment Description</th>
					    					<th>Due Date</th>
					    					<th>Assignment Status</th>
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
					    					<th>Assignment Status</th>
					    				</thead>
				    			<?php
				    		}


				    		// Then regardless of the course, let's output the assignment info
				    		echo "<tr>
				    				<td>" . $row['AssignmentName'] . "</td>
				    				<td>" . $row['AssignmentDescription'] . "</td>
				    				<td>" . $row['FormattedDueDate'] . "</td>";

				    				//Check if user has already taken the assignment
					    				$CheckIfTaken = "SELECT * FROM `LinkUserAssignment` WHERE `UserID` = " . $_SESSION['UserID'] . " AND `AssignmentID` = " . $row['AssignmentID'];
					    				$stm = $DatabaseConnection->prepare($CheckIfTaken);
					    				$stm->execute();
									    $IsAssignmentTakenCount = $stm->rowCount();

									//If the Assignment is both open and user hasn't taken yet, allow him to take it again
						    			if($row['AssignmentOpen'] == "Open" && $IsAssignmentTakenCount == 0){
						    				echo "<td><a href='takeAssignment.php?id=" . $row['AssignmentHash'] . "'>Take Assignment</a></td>";
						    			} else {
						    				echo "<td>Assignment Taken or Closed</td>";
						    			}
				    		echo "</tr>"; 

				    		//Finally, set $CurrentCourse to the actual last course name
			    			$CurrentCourse = $row['CourseName'];
				    	}
				    	//And output the ending </table>
				    	echo "</table>";
				    } else {
				    	echo "<p>Yikes! Looks like you don't have any assignments yet, check back soon for the latest assigments!</p>";
				    }
				?>
			</section>
		</main>
	</body>
</html>