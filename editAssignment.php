<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File editAssignment.php
* Instructor Assignments to edit
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "instructor");

// Check for Edit!
if($_POST['Action'] == "EditAssignmentSettings"){
	// Check Dates OK
	if($_POST['OpenDate'] == "" || $_POST['DueDate'] > $_POST['OpenDate']){
		$OpenDate = "`OpenDate` = '" . SanitizeSQLEntry($_POST['OpenDate']) . "'";
		if($_POST['OpenDate'] == ""){
			$OpenDate = "`OpenDate` = null";
		}
		$Update = "UPDATE `Assignment`
					SET
						`AssignmentName` = '" . SanitizeSQLEntry($_POST['AssignmentName']) . "',
						`AssignmentDescription` = '" . SanitizeSQLEntry($_POST['AssignmentDescription']) . "',
						`CourseID` = '" . SanitizeSQLEntry($_POST['CourseID']) . "',
						`DueDate` = '" . SanitizeSQLEntry($_POST['DueDate']) . "',
						" . $OpenDate . "
					WHERE `AssignmentHash` = '" . SanitizeSQLEntry($_POST['ID']) . "'";
		$stm = $DatabaseConnection->prepare($Update);
		$stm->execute();
		header('Location: editAssignment.php?ID=' . $_POST['ID']);
		exit();
	} else {
		header('Location: editAssignment.php?ID=' . $_POST['ID'] . "&error=Error: Please check Due Date is set Past Open Date!");
	}
}

// Delete Question
if(isset($_GET["Action"]) && $_GET["Action"] == "Delete" && isset($_GET['QID']) && isset($_GET["AID"])){
	$RemoveQ = "DELETE FROM `LinkAssignmentQuestion` WHERE `QuestionID` = " . SanitizeSQLEntry($_GET['QID']) . " AND `AssignmentID` = " . SanitizeSQLEntry($_GET['AID']);
	$stm = $DatabaseConnection->prepare($RemoveQ);
	$stm->execute();
	$_GET['error'] = "Question Removed!";
}

/*
* Check assignment is set and a real course and teacher is the right ID!
*/
$AssignmentQuery = "SELECT 
				C.`CourseID`,
				C.`CourseName`,
				A.`AssignmentID`,
				A.`AssignmentHash`,
				A.`AssignmentName`,
				A.`AssignmentDescription`,
				A.`DueDate`,
				A.`OpenDate`,
				IF(A.`DueDate` > Now(), 'Open', 'Closed') AS `AssignmentOpen`
					FROM 
				`Assignment` AS A 
					INNER JOIN 
				`Course` AS C 
						ON A.`CourseID` = C.`CourseID`
				WHERE A.`AssignmentHash` = '" . SanitizeSQLEntry($_GET['ID']) . "' 
					AND 
				C.`TeacherID` = '" . $_SESSION['UserID'] . "'";
				$stm = $DatabaseConnection->prepare($AssignmentQuery);
$stm->execute();
$Assignment = $stm->fetchAll();
$RowCount = $stm->rowCount();


if(!isset($_GET['ID']) || $_GET['ID'] == "" || $RowCount == 0){
	header('Location: /error.php?error=Sorry that didn\'t work! Please try again&verifymsg=' . CreateAuthenticatedMessageHash() . '');
	exit();
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Edit Assignment | <?php echo $SiteTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="assets/css/main.css?version=5">
		<?php require_once('views/headerAssets.php');?>
		
		<script
			  src="https://code.jquery.com/jquery-3.5.0.min.js"
			  integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ="
			  crossorigin="anonymous"></script>
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
				<h1><strong>Edit Assignment</strong></h1>
				<p><?php echo $Assignment[0]["CourseName"] . " - " . $Assignment[0]["AssignmentName"]; ?></p>
					<br>
					<p>Edit Assignment Settings</p>
					<p class="wst-error-message"><?php if(isset($_GET['error'])) echo $_GET['error']; ?></p>
				<form action="editAssignment.php" method="POST">
					<?php 
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
					?>
					Course:
						<select name="CourseID" required="required" autocomplete='off'>
						<?php
							foreach ($TeacherCourses as $TeacherCourse) {
								echo "<option value='" . $TeacherCourse['CourseID'] . "'";
									if($TeacherCourse['CourseID'] == $Assignment[0]["CourseID"]){ echo "selected='selected'"; };
								echo ">" . $TeacherCourse['CourseName'] . " - " . $TeacherCourse['CourseID'] . "</option>";
							}
						?>
					</select><br>
					Assignment Name: <input type="text" name="AssignmentName" required="required" value="<?php echo $Assignment[0]["AssignmentName"]; ?>">
						<br>
					Assignment Description: <input type="text" name="AssignmentDescription" required="required" value="<?php echo $Assignment[0]["AssignmentDescription"]; ?>">
						<br><br>
					Due Date: <input type="datetime-local" name="DueDate" required="required" value="<?php echo date_format(date_create($Assignment[0]["DueDate"]), "Y-m-d") . "T" . date_format(date_create($Assignment[0]["DueDate"]), "H:i"); ?>" min="<?php echo date_format(date_create(), "Y-m-d") . "T" . date_format(date_create(), "H:i"); ?>">
						<br><i>(Format YYYY-MM-DD HH:MI:SS)</i><br><br>
					Open Date: <input type="datetime-local" name="OpenDate" value="<?php echo date_format(date_create($Assignment[0]["OpenDate"]), "Y-m-d") . "T" . date_format(date_create($Assignment[0]["OpenDate"]), "H:i"); ?>">
						<br><i>Use this to schedule when to open the Assignment to the Students. If you do not want to open, leave it blank. To open now, insert today's date. <br>(Format YYYY-MM-DD HH:MI:SS)</i><br>
						(IMPORTANT: To correct for server errors, please make the start time 3 hours before the actual start time. Sorry for the inconvenience)<br>
					<input type="submit" name="submit" value="Submit">
					<input type="hidden" name="Action" value="EditAssignmentSettings">
					<input type="hidden" name="ID" value="<?php echo $Assignment[0]["AssignmentHash"]; ?>">
				</form>
					<br>
					<br>
				<p>Add/Edit Questions: </p>
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
						LEFT JOIN
					`LinkAssignmentQuestion` AS LAQ
						ON A.`AssignmentID` = LAQ.`AssignmentID`
						LEFT JOIN
					`Question` AS Q
						ON LAQ.`QuestionID` = Q.`QuestionID`
						LEFT JOIN
					`Answer` AS AN
						ON Q.`CorrectAnswer` = AN.`AnswerID`
					WHERE A.`AssignmentHash` = '" . SanitizeSQLEntry($_GET['ID']) . "'";
					$stm = $DatabaseConnection->prepare($Assignment);
				    $stm->execute();
				    $records = $stm->fetchAll();
				    $RowCount = $stm->rowCount();
				    echo "<p>Assignment Status: " . $records[0]["AssignmentOpen"] . "</p><br><br>";
				    echo "<p id='Message'></p>";
				    echo "<input type='hidden' id='AssignmentID' value='" . $records[0]["AssignmentID"] . "'>";
				    echo "<div id='QuestionSection'>";
				    	echo "<div id='Questions'>";
					    foreach ($records as $row) {
					        // Skip the inital NULL Question
					        if($row['QuestionID'] != NULL){
					            //We need to parse the Question Type
        				    		echo "<div id=" . $row['QuestionID'] . ">";
        					    		echo $row['QuestionID'] . ") " . $row['QuestionDescription'] . " <br>";
        					    		echo stripslashes($row['QuestionName']) . "<br>";
        					    		echo " Question Type: ";
        					    			if($row['QuestionType'] == "select"){
        					    				echo "Select/Dropdown<br>";
        					    			} else if($row['QuestionType'] == "fib"){
        					    				echo "Fill in the Blank<br>";
        					    			} else if($row['QuestionType'] == "tf"){
        					    				echo "True or False<br>";
        					    			}
        					    		$sql = "SELECT 
        					    				A.`AnswerName`
        					    					FROM
        					    				`LinkQuestionAnswer` AS LQA
        					    					INNER JOIN
        					    				`Answer` AS A
        					    					ON LQA.`AnswerID` = A.`AnswerID` 
        					    				WHERE 
        					    					LQA.`QuestionID` = " . $row['QuestionID'];
        					    		$stm = $DatabaseConnection->prepare($sql);
        							    $stm->execute();
        							    $answers = $stm->fetchAll();
        							    echo "Correct Answer: " . $row['AnswerName'] . "<br>";
        							    if($row['QuestionType'] == "select"){
        							    	echo "Other Answers: <br>";
        							    		foreach ($answers as $answer) {
        							    			echo "<p>- " . $answer['AnswerName'] . "</p>";
        							    		}
        							    }
        							    echo "<a href='?Action=Delete&QID=" . $row['QuestionID'] . "&AID=" . $records[0]["AssignmentID"] . "&ID=" . $_GET['ID'] ."'>Remove Question</a><br>";
        							    echo "<button onclick='Question(" . $row['QuestionID'] . ")' class=wst-button>Edit Question</button>";
        							echo "</div>";
        							   	echo "<br>";   
					        }
						}
						echo "</div>";
						echo "<button onclick='Question(0)' class=wst-button>Add Question</button>";
					echo "</div>";

						echo "<br><br>";

					echo "<div id='EditSection' style='display:none;'><h2>Edit/Add Question</h2>";
					?>
						<div id="AddEditQuestion" style="text-align: center;">
							<input type="hidden" id="QuestionID" value="0">
							Question Title: <input type="text" id="QuestionDescription" required="required"><br>
							Question: <input type="text" id="QuestionName" required="required"><br>
							Question Type:
								<select id="QuestionType" required="required" onchange="SwitchInputs()">
									<option value="select" selected="selected">Multiple Choice</option>
									<option value="tf">True or False</option>	
									<option value="fib">Fill in the Blank</option>
								</select><br>
							Correct Answer:
								<input type="text" id="CorrectAnswer_FIB"><br>
								<select id="CorrectAnswer_TF" style="display: none">
									<option value="1">True</option>
									<option value="2">False</option>
								</select>
							<span id="AnswersLB">Answer Choices <i>(Note, if answer is Fill in the Blank, please enter only ONE Answer, if T/F leave this blank, and if Multiple choice, enter one answer per line)</i>:</span><br>
								<textarea id="Answers"></textarea><br>
							<button class="wst-button" onclick="SubmitQuestion()">Submit</button>
						</div>
					<?php
						echo "<button onclick='CancelEdit()' class=wst-button>Cancel</button>"; 
					echo "</div>";
				?>
			</section>
		</main>
	</body>
	<script type="text/javascript">
		function Question(QID){
			//Start by swapping Blocks
			$("#QuestionSection").css("display", "none");
			$("#EditSection").css("display", "block"); 
			// Now if it's -1 as QID, stop. Otherwise get data from Server
			if(QID != 0){
				// Get Q Data
					// Now pass to Server
					$.ajax({ 
			        data: {'QuestionID': QID},
			        url: 'AJAXGetQuestionDetails.php', 
			        method: 'POST', 
			        success: function(response){ 
			        	console.log(response);
			                if(response == 0){
			                    //If something went wrong and 0 was sent back, alert the user there is an issue, replace the loading GIF with the button again to sign in.
			                    alert("Yikes, something seems to have gone wrong, please try again or relolad the page.");
			                } else {
			                    //Then this means it worked
			                    //Add Data to page
			                    response = response.split("|,|");
			                    // Add to Inputs
			                    $("#QuestionID").val(response[0]);
			                    $("#QuestionName").val(response[1]);
			                    $("#QuestionDescription").val(response[2]);
			                    $("#QuestionType").val(response[3]);
			                    $("#CorrectAnswer_FIB").val(response[4]);
			                    $("#CorrectAnswer_TF").val(response[5]);
			                    $("#Answers").val(response[6]);
			                    SwitchInputs();
			                    $("#QuestionSection").css("display", "none");
								$("#EditSection").css("display", "block");
								$("#Message").html("Question Added Successfully!");
								// Wipe values?
			                };
			        },
			        error: function(){
			            alert("Yikes! It seems you're having a slight network issue! Please ensure your intenet connection is active!");
			        }
			    }); //End AJAX
			} else {
				// Simply clear the Values in form
				$("#QuestionID").val("0");
                $("#QuestionName").val("");
                $("#QuestionDescription").val("");
                $("#QuestionType").val("select");
                $("#CorrectAnswer_FIB").val("");
                $("#CorrectAnswer_TF").val("");
                $("#Answers").val("");
                SwitchInputs();
			}
		}
		function CancelEdit(){
			$("#QuestionSection").css("display", "block");
			$("#EditSection").css("display", "none");
		}
		function SwitchInputs(){
			var QuestionTypeVal = $("#QuestionType").val();
			if(QuestionTypeVal == "select"){
				$("#CorrectAnswer_FIB").css("display", "block");
				$("#CorrectAnswer_TF").css("display", "none");
				$("#Answers").css("display", "block");
				$("#AnswersLB").css("display", "block");
			} else if(QuestionTypeVal == "tf"){
				$("#CorrectAnswer_FIB").css("display", "none");
				$("#CorrectAnswer_TF").css("display", "block");
				$("#Answers").css("display", "none");
				$("#AnswersLB").css("display", "none");
			} else if(QuestionTypeVal == "fib"){
				$("#CorrectAnswer_FIB").css("display", "block");
				$("#CorrectAnswer_TF").css("display", "none");
				$("#Answers").css("display", "none");
				$("#AnswersLB").css("display", "none");
			}
		}
		function SubmitQuestion(){
			// Get the Vars
			var QuestionID = $("#QuestionID").val();
			var QuestionName = $("#QuestionName").val();
			var QuestionDescription = $("#QuestionDescription").val();
			var QuestionType = $("#QuestionType").val();
			var CorrectAnswerFIB = $("#CorrectAnswer_FIB").val();
			var CorrectAnswerTF = $("#CorrectAnswer_TF").val();
			var OtherAnswers = $("#Answers").val();
			var AssignmentID = $("#AssignmentID").val();

			// Check Inputs not empty
			if(QuestionName == "" || (QuestionType == "select" && (OtherAnswers == "" || CorrectAnswerFIB == "")) || (QuestionType == "fib" && (CorrectAnswerFIB == "") ) ){
				alert("Please enter all values properly!");
				return;
			}

			// Now pass to Server
			$.ajax({ 
	        data: {'QuestionID': QuestionID, 'QuestionName': QuestionName, 'QuestionDescription': QuestionDescription, 'QuestionType' : QuestionType, 'CorrectAnswerFIB': CorrectAnswerFIB, 'CorrectAnswerTF': CorrectAnswerTF, 'OtherAnswers': OtherAnswers, 'AssignmentID': AssignmentID},
	        url: 'AJAXEditQuestion.php', 
	        method: 'POST', 
	        success: function(Response){ 
	        	console.log(Response);
	                if(Response == 0){
	                    //If something went wrong and 0 was sent back, alert the user there is an issue, replace the loading GIF with the button again to sign in.
	                    alert("Yikes, something seems to have gone wrong, please try again or relolad the page.");
	                } else {
	                    //Then this means it worked, this won't really happen as we send a succefull response code back with the long polling not this.
	                    //Add Data to page
	                    if(QuestionID != 0){
	                    	$("#" + QuestionID).html(Response);
	                    } else {
							$("#Questions").append(Response);	
	                    }

	                    $("#QuestionSection").css("display", "block");
						$("#EditSection").css("display", "none");
						$("#Message").html("Question Added Successfully!");
						// Wipe values?
	                };
	        },
	        error: function(){
	            alert("Yikes! It seems you're having a slight network issue! Please ensure your intenet connection is active!");
	        }
	    }); //End AJAX
		}
	</script>
	</html>