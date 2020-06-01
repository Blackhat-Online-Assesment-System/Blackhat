<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File reviewAssignmentGrades.php
* Display all grades from assignment
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "instructor");

$Query = "SELECT 
	U.`UserID`,
	U.`UserFullName`,
	U.`Email`,
	U.`SchoolID`, 
	A.`AssignmentID`,
	A.`AssignmentName`,
	A.`AssignmentHash`,
	LUA.`QuestionsCorrect`,
	LUA.`OverallGrade`								
FROM 
	`Assignment` AS A
    	LEFT JOIN
    `LinkUserAssignment` AS LUA
    	ON LUA.`AssignmentID` = A.`AssignmentID`
    	INNER JOIN
    `Users` AS U
    	ON U.`UserID` = LUA.`UserID`
WHERE
	A.`AssignmentHash` = '" . SanitizeSQLEntry($_GET['ID']) . "'";
$stm = $DatabaseConnection->prepare($Query);
$stm->execute();
$Assignments = $stm->fetchAll();
$RowCount = $stm->rowCount();
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
				<h1><strong>Assignment Name: <?php echo $Assignments[0]['AssignmentName']; ?></strong></h1>
					<br>
					<br>
				<?php
				    if($RowCount > 0){
				    	?>
	    					<br>
	    				<table class='align-center'>
		    				<thead>
		    					<th>Student Name</th>
		    					<th>Student Email</th>
		    					<th>Student ID</th>
		    					<th>Questions Correct</th>
		    					<th>Overall Grade</th>
		    					<th>Review Assignment</th>
		    				</thead>
		    				<?php
				    	//Then Loop the Assignments
				    	foreach ($Assignments as $Assignment) {
				    		?>
			    			<?php
								echo "<tr>
					    				<td>" . $Assignment['UserFullName'] . "</td>
					    				<td>" . $Assignment['Email'] . "</td>
					    				<td>" . $Assignment['SchoolID'] . "</td>
					    				<td>" . $Assignment['QuestionsCorrect'] . "</td>
					    				<td>" . number_format((float)$Assignment['OverallGrade']*100, 2, '.', '') . "</td>
					    				<td><a href='instructorReviewAssignment.php?AID=" . $Assignment['AssignmentHash'] . "&UID=" . $Assignment['UserID'] . "'>Review</a>";
					    		echo "</tr>"; 
					    	}
				    	//And output the ending </table>
				    	echo "</table>";
				    	echo "<br><a href='exportGrades.php?AID=" . $Assignment['AssignmentHash'] . "'><button class='wst-button'>Export Results</button></a>";
				    } else {
				    	echo "<p>No Grades Found!</p>";
				    }
				?>
			</section>
		</main>
	</body>
</html>