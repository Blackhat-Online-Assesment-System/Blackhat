<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File takeAssignment.php
* Allow User to take an assignment
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "student");

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Take Assignment | <?php echo $SiteTitle; ?></title>
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
				<h1><strong>Take Assignment</strong></h1>
					<br>
					<br>
				<?php
					$Assignment = "SELECT
						A.`AssignmentID`,
						A.`AssignmentHash`,
						A.`AssignmentName`,
						A.`AssignmentDescription`,
						Q.`QuestionID`,
						Q.`QuestionName`,
						Q.`QuestionType`,
						Q.`QuestionDescription`,
						IF(`DueDate` > Now(), 'Open', 'Closed') AS `AssignmentOpen`
					FROM `Assignment` AS A 
						INNER JOIN
					`LinkAssignmentQuestion` AS LAQ
						ON A.`AssignmentID` = LAQ.`AssignmentID`
						INNER JOIN
					`Question` AS Q
						ON LAQ.`QuestionID` = Q.`QuestionID`
					WHERE A.`AssignmentHash` = '" . SanitizeSQLEntry($_GET['id']) . "'
    					ORDER BY RAND()";
					$stm = $DatabaseConnection->prepare($Assignment);
				    $stm->execute();
				    $records = $stm->fetchAll();
				    
				    //Check the Assignment Hash is correct and that the date hasn't passed
				    if($stm->rowCount() == 0){
				    	header('Location: error.php?error=Incorrect Assignment ID&verifymsg=' . CreateAuthenticatedMessageHash());
				    } else if($row['AssignmentOpen'] == "Closed"){
				    	header('Location: error.php?error=Assignment Date Passed&verifymsg=' . CreateAuthenticatedMessageHash());
				    }

				    //Now check he hasn't already taken this assignment
				    $CheckTaken = "SELECT * FROM `LinkUserAssignment` WHERE `UserID` = " . $_SESSION['UserID'] . ", `AssignmentID` = " . $records[0]["AssignmentID"];
				    $stm = $DatabaseConnection->prepare($CheckTaken);
				    $stm->execute();
				    if($stm->rowCount() > 0){
				    	header('Location: error.php?error=Oops! You\'ve already taken this assignment!&verifymsg=' . CreateAuthenticatedMessageHash());
				    }

				    echo "<h1>" . $records[0]["AssignmentName"] . "</h1><br><br>";

				    $QuestionNumber = 0;
				    //Otherwise! Let's take the assignment
				   	echo "<form action='submitAssignment.php' method='post'>";
				   	echo "<input type='hidden' name='AssignmentHash' value ='" . $_GET['id'] . "'>";
				    foreach ($records as $row) {
				    	//We need to parse the Question Type
				    		echo ++$QuestionNumber . ") " . $row['QuestionDescription'] . ": <br>";
				    		echo "<span style='font-decoration:italics'>" . $row['QuestionName'] . "</span><br>";
				    	if($row['QuestionType'] == "fib"){
				    		echo "<input type='text' name='qid_" . $row['QuestionID'] . "'><br><br>";
				    	} else if($row['QuestionType'] == "tf"){
				    		echo "<select name='qid_" . $row['QuestionID'] . "'><option value='1'>True</option><option value='2'>False</option></select><br><br>";
				    	} else if($row['QuestionType'] == "select"){
				    		//Get the possible answers
				    		$Query = "SELECT *
				    					FROM 
				    					`LinkQuestionAnswer` AS LQA
				    						INNER JOIN
				    					`Answer` AS A
				    						ON LQA.`AnswerID` = A.`AnswerID`
				    					WHERE LQA.`QuestionID` = " . $row['QuestionID'] . "
				    					ORDER BY RAND()";
				    		$stm = $DatabaseConnection->prepare($Query);
						    $stm->execute();
						    $answers = $stm->fetchAll();
						    echo "<select name='qid_" . $row['QuestionID'] . "'>";
						    foreach ($answers as $answer) {
						    	echo "<option value=" . $answer['AnswerID'] . ">" . $answer['AnswerName'] . "</option>";
						    }
						    echo "</select><br><br>";
				    	}
				    }
				   	echo "<input type='submit' value='Submit Assignment'>";
				    echo "</form>";
				?>
			</section>
		</main>
	</body>
</html>