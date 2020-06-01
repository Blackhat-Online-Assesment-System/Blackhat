<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File reviewAssignment.php
* Allow User to review an assignment
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "student");

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Review Assignment | CS Project</title>
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
				<h1><strong>Review Assignment</strong></h1>
					<br>
					<br>
				<?php
					$Assignment = "SELECT
						A.`AssignmentID`,
						A.`AssignmentHash`,
						A.`AssignmentName`,
						AN.`AnswerName`,
						A.`AssignmentDescription`,
						Q.`QuestionID`,
						Q.`QuestionName`,
						Q.`QuestionType`,
						Q.`QuestionDescription`,
					IF(A.`DueDate` > Now(), 'Open', 'Closed') AS `AssignmentOpen`
					FROM `Assignment` AS A 
						INNER JOIN
					`LinkAssignmentQuestion` AS LAQ
						ON A.`AssignmentID` = LAQ.`AssignmentID`
						INNER JOIN
					`Question` AS Q
						ON LAQ.`QuestionID` = Q.`QuestionID`
						INNER JOIN
					`Answer` AS AN
						ON Q.`CorrectAnswer` = AN.`AnswerID`
					WHERE A.`AssignmentHash` = '" . SanitizeSQLEntry($_GET['id']) . "'";
					$stm = $DatabaseConnection->prepare($Assignment);
				    $stm->execute();
				    $records = $stm->fetchAll();
				    $RowCount = $stm->rowCount();
				    //Check the Assignment Hash is correct and that the date hasn't passed
				    if($RowCount == 0){
				    	header('Location: error.php?error=Incorrect Assignment ID&verifymsg=' . CreateAuthenticatedMessageHash());
				    } else if($records[0]['AssignmentOpen'] == "Open"){
				    	//If open we don't want to show yet (otherwise can send correct answers to other students)
				    	header('Location: error.php?error=Assignment Date has not passed yet&verifymsg=' . CreateAuthenticatedMessageHash());
				    }
				    //Otherwise show the assignment 
				    echo "<h1>" . $records[0]["AssignmentName"] . "</h1><br><br>";
				    //Calculate the Number or Q's and Number correct so we know final grade
				    $QuestionNumber = 0;
				    $NumberCorrect = 0;
				    foreach ($records as $row) {
				    	//We need to parse the Question Type
			    		echo ++$QuestionNumber . ") " . $row['QuestionName'] . ": <br>";

			    		$sql = "SELECT 
			    				A.`AnswerName`,
			    				LUA.`FIBValue`
			    				FROM `LinkUserAnswer` AS LUA
			    					INNER JOIN
			    				`Answer` AS A
			    					ON LUA.`AnswerID` = A.`AnswerID` 
			    				WHERE 
			    					LUA.`UserID` = " . $_SESSION['UserID'] . " AND
			    					LUA.`AssignmentID` = " . $row['AssignmentID'] . " AND
			    					LUA.`QuestionID` = " . $row['QuestionID'];
			    		$stm = $DatabaseConnection->prepare($sql);
					    $stm->execute();
					    $answers = $stm->fetchAll();
					    echo "Your Answer: ";
					    	if($row['QuestionType'] == "fib"){
					    		echo $answers[0]["FIBValue"];
					    	} else {
					    		echo $answers[0]['AnswerName'];
					    	}
					   	echo "<br>";
					    echo "Correct Answer: " . $row['AnswerName'] . "<br>";
					    if((strtolower($row['AnswerName']) == strtolower($answers[0]["FIBValue"])) || (strtolower($row['AnswerName']) == strtolower($answers[0]["AnswerName"]))){
					    	echo "Answered Correctly - Yes<br>";
					    	++$NumberCorrect; 
					    } else {
					    	echo "Answered Correctly - No<br>";
					    }
					   	echo "<br>";
					}
					   echo "Total Correct: " . $NumberCorrect . "<br>";
					   echo "Total Score: " . ($NumberCorrect / $QuestionNumber) * 100;				  
				?>
			</section>
		</main>
	</body>
</html>