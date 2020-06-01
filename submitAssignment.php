<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File submitAssignment.php
* Submit User's assignment
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "student");

// Include Mailer Headers
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'mailer/src/Exception.php';
require 'mailer/src/PHPMailer.php';
require 'mailer/src/SMTP.php';

//Note: We add 5 minutes in case of a late submission
	$Assignment = "SELECT
		A.`AssignmentID`,
		A.`AssignmentHash`,
		A.`AssignmentName`,
		A.`AssignmentDescription`,
		Q.`QuestionID`,
		Q.`QuestionName`,
		Q.`QuestionType`,
		Q.`QuestionDescription`,
		Q.`CorrectAnswer`,
		AN.`AnswerName`,
	IF(DATE_ADD(`DueDate`, INTERVAL 5 MINUTE) > CURDATE(), 'Open', 'Closed') AS `AssignmentOpen`
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
	WHERE A.`AssignmentHash` = '" . SanitizeSQLEntry($_POST['AssignmentHash']) . "'";
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
//Now keep track of the # of correct Q's
$NumberOfQuestionsRight = 0;
$NumberOfQuestions = 0;
//Otherwise we need to submit
foreach ($records as $row) {
	$NumberOfQuestions++;
		//Fill in the Blank
	if($row['QuestionType'] == "fib"){
		//Submit as FIB
		$sql = "INSERT INTO `LinkUserAnswer` (UserID, AssignmentID, QuestionID, AnswerID, FIBValue) VALUES (" . $_SESSION['UserID'] . "," . $row['AssignmentID'] . "," . $row['QuestionID'] . ",3,'" . SanitizeSQLEntry($_POST['qid_' . $row['QuestionID']]) . "')";
		if(strtolower($_POST['qid_' . $row['QuestionID']]) == strtolower($row['AnswerName'])){
			$NumberOfQuestionsRight++;
		}

	} else if($row['QuestionType'] == "tf"){
		//True or False
		$sql = "INSERT INTO `LinkUserAnswer` (UserID, AssignmentID, QuestionID, AnswerID, FIBValue) VALUES (" . $_SESSION['UserID'] . "," . $row['AssignmentID'] . "," . $row['QuestionID'] . ",". SanitizeSQLEntry($_POST['qid_' . $row['QuestionID']]) .",'NULL')";
		if($_POST['qid_' . $row['QuestionID']] == $row['CorrectAnswer']){
			$NumberOfQuestionsRight++;
		}
	} else if($row['QuestionType'] == "select"){
		//Dropdown
		$sql = "INSERT INTO `LinkUserAnswer` (UserID, AssignmentID, QuestionID, AnswerID, FIBValue) VALUES (" . $_SESSION['UserID'] . "," . $row['AssignmentID'] . "," . $row['QuestionID'] . ",". SanitizeSQLEntry($_POST['qid_' . $row['QuestionID']]) .",'NULL')";
		if($_POST['qid_' . $row['QuestionID']] == $row['CorrectAnswer']){
			$NumberOfQuestionsRight++;
		}
	}
	$stm = $DatabaseConnection->prepare($sql);
	$stm->execute();
}

//Finally mark the assignment as done
$MarkAsDone = "INSERT INTO `LinkUserAssignment` (UserID, AssignmentID, QuestionsCorrect, OverallGrade) VALUES (" . $_SESSION['UserID'] . "," . $row['AssignmentID'] . "," . $NumberOfQuestionsRight . "," . ($NumberOfQuestionsRight / $NumberOfQuestions) . ")";
$stm = $DatabaseConnection->prepare($MarkAsDone);
$stm->execute();

$ConfirmationEmail = "<!DOCTYPE HTML>
<html>
<style>

	body {
	background: linear-gradient(-45deg, #000000, #696969, #808080, #A9A9A9);
	background-size: 400% 400%;
	animation: gradient 15s ease infinite;
	}

	@keyframes gradient {
		0% {
			background-position: 0% 50%;
		}
		50% {
			background-position: 100% 50%;
		}
		100% {
			background-position: 0% 50%;
		}
	}

	h1 {
		font-family: 'Arial';
		color: white;
		text-align: center;
	}
	p {
		font-family: 'Arial';
		color: white;
		text-align: center;
	}
	.ConfirmationButtonDiv {
		text-align: center;
	}
	.ConfirmationButton {
		background-color: black;
		border: none;
		color: white;
		padding: 15px 32px;
  		text-align: center;
  		text-decoration: none;
  		display: inline-block;
  		font-size: 16px;
	}
	.ConfirmationButton:hover {
		background-color: grey;
		color: black;
		cursor: pointer;
	}

	.ActivationLink {
		color: white;
	}
	.ActivationLink:hover {
		color: black;
	}
</style>
<body>
	<h1>Hi,". $_SESSION['UserFullName']."! This email is to confirm that you submitted the following assignment: <br>
	</h1>
	<p>" . $records[0]['AssignmentName'] . "</p>
	<br><br><br>
</body>
</html>
";

	try{
		$mail = new PHPMailer();
		$mail->SMTPDebug = $SMTP_Debug;
		$mail->isSMTP();                            
		$mail->Host = $SMTP_Host;                       
		$mail->SMTPAuth = $SMTP_Auth;                     
		$mail->Username = $SMTP_Auth;                      
		$mail->Password = $SMTP_Password;                   
		$mail->SMTPSecure = $SMTP_Security;                     
		$mail->Port = $SMTP_Port;                       
		//Recipients
		$mail->setFrom($SMTP_SendEmailsFromAddress, $SiteTitle);                         
		$mail->addAddress($records[0]["Email"]);   

		//Content
		$mail->isHTML(true); // Set email format to HTML

		$mail->Subject   = 'Blackhat - Password Reset';
		$mail->Body      = $ConfirmationEmail;
		$mail->Send();
	} catch (Exception $e) {
		echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo . '<br />';
	};

header('Location: message.php?Message=SubmitAssignmentSuccess&MessageVerify=' . CreateAuthenticatedMessageHash());
?>