<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File AJAXGetQuestionDetails.php
* Using AJAX, Get details of a Question
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "instructor");

// Check POST
if(!isset($_POST['QuestionID'])){
	echo "0";
	exit();
} else {
	$Q = "SELECT 
			Q.`QuestionName`,
			Q.`QuestionDescription`,
			Q.`QuestionType`,
			Q.`CorrectAnswer`,
			A.`AnswerName`
		FROM 
			`Question` AS Q
				INNER JOIN
			`Answer` AS A
				ON A.`AnswerID` = Q.`CorrectAnswer`
		WHERE 
			`QuestionID` = '" . SanitizeSQLEntry($_POST['QuestionID']) . "'";
	$stm = $DatabaseConnection->prepare($Q);
    $stm->execute();
    $Q_Data = $stm->fetchAll();

    
    $Q_Data = $Q_Data[0];
    $QuestionID = SanitizeSQLEntry($_POST['QuestionID']);
    $QuestionName = $Q_Data['QuestionName'];
    $QuestionDescription = $Q_Data['QuestionDescription'];
    $QuestionType = $Q_Data['QuestionType'];

    if($QuestionType == "fib"){
    	$CorrectAnswerFIB = $Q_Data['AnswerName'];
    	$CorrectAnswerTF = null;
    	$OtherAnswers = null;
    } else if($QuestionType == "tf"){
    		if($CorrectAnswerTF == "true"){
    			$CorrectAnswerTF = "1";
    		} else {
    			$CorrectAnswerTF = "2";
    		}
    	$CorrectAnswerFIB = null;
    	$OtherAnswers = null;
    } else if($QuestionType == "select"){
    	$CorrectAnswerTF = null;
    	$CorrectAnswerFIB = $Q_Data['AnswerName'];
    		$Query = "SELECT *
					FROM 
					`LinkQuestionAnswer` AS LQA
						INNER JOIN
					`Answer` AS A
						ON LQA.`AnswerID` = A.`AnswerID`
					WHERE LQA.`QuestionID` = " . SanitizeSQLEntry($_POST['QuestionID']) . "
					ORDER BY RAND()";
			$stm = $DatabaseConnection->prepare($Query);
		    $stm->execute();
		    $answers = $stm->fetchAll();

		    foreach ($answers as $answer) {
		    	$OtherAnswers .= $answer['AnswerName'] . "\n";
		    }	
    }
        echo $QuestionID . "|,|" . $QuestionName . "|,|" . $QuestionDescription . "|,|" . $QuestionType . "|,|" . $CorrectAnswerFIB . "|,|" . $CorrectAnswerTF . "|,|" . $OtherAnswers;
    }
?>