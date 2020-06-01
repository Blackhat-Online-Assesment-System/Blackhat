<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File exportGrades.php
* Export Assignment Grades to CSV
*/

// Include System Functions and Initiate the System!
require_once('functions.php');
InitiateSystem(false, "instructor");

header('Content-type: text/plain');

// Helper function to turn Array into a CSV Download
function array_to_csv_download($array, $filename = "export.csv", $delimiter=",") {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    $f = fopen('php://output', 'w');
    foreach ($array as $line) {
        fputcsv($f, $line, $delimiter);
    }
}

$Query = "SELECT 
			U.`UserID`,
			U.`UserFullName`,
			U.`Email`,
			U.`SchoolID`,
			A.`AssignmentName`, 
			A.`AssignmentID`,
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
			A.`AssignmentHash` = '" . SanitizeSQLEntry($_GET['AID']) . "'";
			$stm = $DatabaseConnection->prepare($Query);
			$stm->execute();
			$Assignment = $stm->fetchAll();
			$RowCount = $stm->rowCount();
	
	$CSVArray = array();
	
	$Headers1 = array($SiteTitle . " - Assignment Results: '" . $Assignment[0]["AssignmentName"] . "'", "Generated Date: " . date("l jS F Y h:i:s") . ""); 
	$Headers2 = array("Student Name", "Student Email", "Student ID", "Questions Correct", "Overall Grade");
		array_push($CSVArray, $Headers1);
		array_push($CSVArray, $Headers2);	
	
	foreach ($Assignment as $row) {
		$LineArray = array($row['UserFullName'], $row['Email'], $row['SchoolID'], $row['QuestionsCorrect'], (number_format((float)$row['OverallGrade']*100, 2, '.', '')));
		array_push($CSVArray, $LineArray);
	}
	
	array_to_csv_download($CSVArray, $SiteTitle . "_Assignment_Results_" . $Assignment[0]["AssignmentName"] . date("l_jS_F_Y_h:i:s") . ".csv");
	
?>