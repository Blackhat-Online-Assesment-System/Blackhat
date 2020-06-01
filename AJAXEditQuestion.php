<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File AJAXEditQuestion.php
* Using AJAX, Get an Edit or Add Question to an Assignment to edit it
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "instructor");

// Check POST
if(!isset($_POST['QuestionID'])){
	echo "0";
	exit();
} else {
	$QuestionID = SanitizeSQLEntry($_POST['QuestionID']);
	$QuestionName = SanitizeSQLEntry($_POST['QuestionName']);
	$QuestionDescription = SanitizeSQLEntry($_POST['QuestionDescription']);
	$QuestionType = SanitizeSQLEntry($_POST['QuestionType']);
	$CorrectAnswerFIB = SanitizeSQLEntry($_POST['CorrectAnswerFIB']);
	$CorrectAnswerTF = SanitizeSQLEntry($_POST['CorrectAnswerTF']);
	$OtherAnswers = SanitizeSQLEntry($_POST['OtherAnswers']);
	$AssignmentID = SanitizeSQLEntry($_POST['AssignmentID']);

	// Get Assignment Hash
	$GetAssgnHash = "SELECT AssignmentHash FROM Assignment WHERE `AssignmentID` = " . $AssignmentID;
	$stm = $DatabaseConnection->prepare($GetAssgnHash);
	$stm->execute();
	$AssignmentHash = $stm->fetchAll()[0]["AssignmentHash"];

	// If new Q
	if($QuestionID == "0"){
		if($QuestionType == "fib"){
			// Add CorrectAnswer
				$AddAnswer = "INSERT INTO `Answer` (AnswerName) VALUES ('" . $CorrectAnswerFIB . "')";
				$stm = $DatabaseConnection->prepare($AddAnswer);
				$stm->execute();
				$AID = $DatabaseConnection->lastInsertId();
			// Then Add Question and Link to Answer
				$AddQuestion = "INSERT INTO `Question` (QuestionType, QuestionName, QuestionDescription, CorrectAnswer) VALUES ('" . $QuestionType . "', '" . $QuestionName . "', '" . $QuestionDescription . "', '" . $AID . "')";
				$stm = $DatabaseConnection->prepare($AddQuestion);
				$stm->execute();
				$QID = $DatabaseConnection->lastInsertId();
			// Then Link Q to Assignment
				$LinkToAssignment = "INSERT INTO `LinkAssignmentQuestion` (AssignmentID, QuestionID, Status) VALUES ('" . $AssignmentID . "', '" . $QID . "', '1')";
				$stm = $DatabaseConnection->prepare($LinkToAssignment);
				$stm->execute();
		} else if($QuestionType == "tf"){
			// Answers added as INT's 1,2
				$AddQuestion = "INSERT INTO `Question` (QuestionType, QuestionName, QuestionDescription, CorrectAnswer) VALUES ('" . $QuestionType . "', '" . $QuestionName . "', '" . $QuestionDescription . "', '" . $CorrectAnswerTF . "')";
				$stm = $DatabaseConnection->prepare($AddQuestion);
				$stm->execute();
				$QID = $DatabaseConnection->lastInsertId();
			// Then Link Q to Assignment
				$LinkToAssignment = "INSERT INTO `LinkAssignmentQuestion` (AssignmentID, QuestionID, Status) VALUES ('" . $AssignmentID . "', '" . $QID . "', '1')";
				$stm = $DatabaseConnection->prepare($LinkToAssignment);
				$stm->execute();
		} else if($QuestionType == "select"){
			// 1. Add Question
				$AddQuestion = "INSERT INTO `Question` (QuestionType, QuestionName, QuestionDescription, CorrectAnswer) VALUES ('" . $QuestionType . "', '" . $QuestionName . "', '" . $QuestionDescription . "', '-1')";
				$stm = $DatabaseConnection->prepare($AddQuestion);
				$stm->execute();
				$QID = $DatabaseConnection->lastInsertId();
			// 2. Add Answers and Link to QID, Find C.AID
				// For each answer we insert into the DB, if $Ans = $CorrAns, store ID, if none match, add $CorrectAns
				$OtherAnswers = trim($OtherAnswers);
				$OtherAnswersAR = explode("\n", $OtherAnswers);
				$OtherAnswersAR = array_filter($OtherAnswersAR, 'trim'); // remove any extra \r characters left behind
			$CorrAnswID = -1;
			foreach ($OtherAnswersAR as $Answer) {
			    //Add
			    $AddAnswer = "INSERT INTO `Answer` (AnswerName) VALUES ('" . $Answer . "')";
				$stm = $DatabaseConnection->prepare($AddAnswer);
				$stm->execute();
				$AID = $DatabaseConnection->lastInsertId();
				//Link
				$LinkQtoA = "INSERT INTO `LinkQuestionAnswer` (QuestionID, AnswerID, Status) VALUES ('" . $QID . "', '" . $AID . "', '1')";
				$stm = $DatabaseConnection->prepare($LinkQtoA);
				$stm->execute();
				// Check for AID
				if($CorrectAnswerFIB == $Answer){
					$CorrAnswID = $AID;
				}
			} 
			// Check CorrAnsw added
			if($CorrAnswID == -1){
				$AddAnswer = "INSERT INTO `Answer` (AnswerName) VALUES ('" . $CorrectAnswerFIB . "')";
				$stm = $DatabaseConnection->prepare($AddAnswer);
				$stm->execute();
				$AID = $DatabaseConnection->lastInsertId();
				//Link
				$LinkQtoA = "INSERT INTO `LinkQuestionAnswer` (QuestionID, AnswerID, Status) VALUES ('" . $QID . "', '" . $AID . "', '1')";
				$stm = $DatabaseConnection->prepare($LinkQtoA);
				$stm->execute();
				$CorrAnswID = $AID;
			}
			// Now Update QID
				$UpdateQ = "UPDATE `Question` SET `CorrectAnswer` = '" . $CorrAnswID . "' WHERE `QuestionID` = '" . $QID . "'";
				$stm = $DatabaseConnection->prepare($UpdateQ);
				$stm->execute();
			// Then Link Q to Assignment
				$LinkToAssignment = "INSERT INTO `LinkAssignmentQuestion` (AssignmentID, QuestionID, Status) VALUES ('" . $AssignmentID . "', '" . $QID . "', '1')";
				$stm = $DatabaseConnection->prepare($LinkToAssignment);
				$stm->execute();
		}
		$QuestionID = $QID;
	} else {
		// Update Q
		if($QuestionType == "fib"){
			// Check for CorrwAnsw OR Add new and Update
			$CheckForCorrAnsw = "SELECT `AnswerName`, `AnswerID` FROM `Answer` WHERE `AnswerName` = '" . $CorrectAnswerFIB . "'";
			$stm = $DatabaseConnection->prepare($CheckForCorrAnsw);
			$stm->execute();
			$CheckForCorrAnsw = $stm->fetchAll();
			$RowCount = $stm->rowCount();
			$CorrwAnsw;
			if($RowCount > 0){
				// Meaning AID is there
				$CorrwAnsw = $CheckForCorrAnsw[0]["AnswerID"];
			} else {
				$AddAnswer = "INSERT INTO `Answer` (AnswerName) VALUES ('" . $CorrectAnswerFIB . "')";
				$stm = $DatabaseConnection->prepare($AddAnswer);
				$stm->execute();
				$CorrwAnsw = $DatabaseConnection->lastInsertId();
			}
			$UpdateQ = "UPDATE `Question` SET `QuestionType` = '" . $QuestionType . "', `QuestionName` = '" . $QuestionName . "', `QuestionDescription` = '" . $QuestionDescription . "', `CorrectAnswer` = '" . $CorrwAnsw . "'";
			$stm = $DatabaseConnection->prepare($UpdateQ);
			$stm->execute();
		} else if($QuestionType == "tf"){
			$UpdateQ = "UPDATE `Question` SET `QuestionType` = '" . $QuestionType . "', `QuestionName` = '" . $QuestionName . "', `QuestionDescription` = '" . $QuestionDescription . "', `CorrectAnswer` = '" . $CorrectAnswerTF . "'";
			$stm = $DatabaseConnection->prepare($UpdateQ);
			$stm->execute();
		} else if($QuestionType == "select"){
			//0. Drop all previous answers for Question
			$Query = "DELETE FROM `LinkQuestionAnswer` WHERE `QuestionID` = '" . $QuestionID . "'";
			$stm = $DatabaseConnection->prepare($Query);
			$stm->execute();

			// 1. Add Answers and Link to QID, Find C.AID
				// For each answer we insert into the DB, if $Ans = $CorrAns, store ID, if none match, add $CorrectAns
				$OtherAnswers = trim($OtherAnswers);
				$OtherAnswersAR = explode("\n", $OtherAnswers);
				$OtherAnswersAR = array_filter($OtherAnswersAR, 'trim'); // remove any extra \r characters left behind
			$CorrAnswID = -1;
			foreach ($OtherAnswersAR as $Answer) {
						// Add and Link!
					$AddAnswer = "INSERT INTO `Answer` (AnswerName) VALUES ('" . $Answer . "')";
					$stm = $DatabaseConnection->prepare($AddAnswer);
					$stm->execute();
					$AID = $DatabaseConnection->lastInsertId();
						//Link
					$LinkQtoA = "INSERT INTO `LinkQuestionAnswer` (QuestionID, AnswerID, Status) VALUES ('" . $QuestionID . "', '" . $AID . "', '1')";
					$stm = $DatabaseConnection->prepare($LinkQtoA);
					$stm->execute();

				// Check for AID
				if($CorrectAnswerFIB == $Answer){
					$CorrAnswID = $AID;
				}
			}
			// Check CorrAnsw added
			if($CorrAnswID == -1){
				$AddAnswer = "INSERT INTO `Answer` (AnswerName) VALUES ('" . $CorrectAnswerFIB . "')";
				$stm = $DatabaseConnection->prepare($AddAnswer);
				$stm->execute();
				$AID = $DatabaseConnection->lastInsertId();
				//Link
				$LinkQtoA = "INSERT INTO `LinkQuestionAnswer` (QuestionID, AnswerID, Status) VALUES ('" . $QuestionID . "', '" . $AID . "', '1')";
				$stm = $DatabaseConnection->prepare($LinkQtoA);
				$stm->execute();
				$CorrAnswID = $AID;
			}
			// Update Q
			$UpdateQ = "UPDATE `Question` SET `QuestionType` = '" . $QuestionType . "', `QuestionName` = '" . $QuestionName . "', `QuestionDescription` = '" . $QuestionDescription . "', `CorrectAnswer` = '" . $CorrAnswID . "' WHERE `QuestionID` = '" . $QuestionID . "'";
			$stm = $DatabaseConnection->prepare($UpdateQ);
			$stm->execute();
		}
	}
	// FINNALY!
	// Return Data
		$Return = "<div id=" . $QuestionID . ">";
		$Return .= $QuestionID . ") " . $QuestionName . "<br>";
		$Return .= "Question Type: ";
			if($QuestionType == "select"){
				$Return .= "Select/Dropdown<br>";
				$Return .= "Correct Answer: " . $CorrectAnswerFIB . "<br>";
			} else if($QuestionType == "fib"){
				$Return .= "Fill in the Blank<br>";
				$Return .= "Correct Answer: " . $CorrectAnswerFIB . "<br>";
			} else if($QuestionType == "tf"){
				$Return .= "True or False<br>";
				$Return .= "Correct Answer: ";
					if($CorrectAnswerTF == 1){
						$Return .= "True";
					} else {
						$Return .= "False";
					}
					$Return .= "<br>";
			}
			if($QuestionType == "select"){
		    	$Return .= "Other Answers: <br>";
		    		foreach ($OtherAnswersAR as $Answer) {
		    			$Return .= "<p>- " . $Answer . "</p>";
		    		}
		    }
		    $Return .= "<a href='?Action=Delete&QID=" . $QuestionID . "&AID=" . $AssignmentID . "&ID=" . $AssignmentHash . "'>Remove Question</a><br>";
		    $Return .= "<button onclick='Question(" . $QuestionID . ")' class=wst-button>Edit Question</button>";
			$Return .= "</div>";
			$Return .= "<br>";
			// Return Data
			echo $Return;
		}
?>