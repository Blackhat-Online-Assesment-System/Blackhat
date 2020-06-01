CREATE TABLE `Answer` (
  `AnswerID` int(9) NOT NULL,
  `AnswerName` varchar(140) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Assignment` (
  `AssignmentID` int(9) NOT NULL,
  `AssignmentHash` varchar(400) DEFAULT NULL,
  `CourseID` int(9) NOT NULL,
  `AssignmentName` varchar(300) NOT NULL,
  `AssignmentDescription` varchar(500) NOT NULL,
  `OpenDate` datetime DEFAULT NULL,
  `DueDate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Course` (
  `CourseID` int(9) NOT NULL,
  `TeacherID` int(9) NOT NULL,
  `CourseName` varchar(300) DEFAULT NULL,
  `CourseDescription` varchar(500) DEFAULT NULL,
  `ClassTime` varchar(300) DEFAULT NULL,
  `CourseJoinCode` varchar(300) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `LinkAssignmentQuestion` (
  `AssignmentID` int(9) NOT NULL,
  `QuestionID` int(9) NOT NULL,
  `Status` int(9) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `LinkQuestionAnswer` (
  `QuestionID` int(9) NOT NULL,
  `AnswerID` int(9) NOT NULL,
  `Status` int(9) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `LinkUserAnswer` (
  `UserID` int(9) NOT NULL,
  `AssignmentID` int(9) NOT NULL,
  `QuestionID` int(9) NOT NULL,
  `AnswerID` int(9) NOT NULL,
  `FIBValue` varchar(400) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `LinkUserAssignment` (
  `UserID` int(9) NOT NULL,
  `AssignmentID` int(9) NOT NULL,
  `AssignmentStatus` int(9) NOT NULL,
  `QuestionsCorrect` int(9) NOT NULL,
  `OverallGrade` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `LinkUserCourse` (
  `UserID` int(9) NOT NULL,
  `CourseID` int(9) NOT NULL,
  `UserRole` varchar(140) NOT NULL,
  `Status` int(9) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Question` (
  `QuestionID` int(9) NOT NULL,
  `QuestionType` varchar(50) NOT NULL,
  `QuestionName` varchar(300) NOT NULL,
  `QuestionDescription` varchar(300) DEFAULT NULL,
  `CorrectAnswer` int(9) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Users` (
  `UserID` int(9) NOT NULL,
  `UserFullName` varchar(300) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Password` varchar(200) NOT NULL,
  `TwoFactorEnabled` varchar(1) DEFAULT '0',
  `TFASecret` varchar(32) DEFAULT NULL,
  `UserRoles` varchar(300) DEFAULT NULL,
  `SchoolID` varchar(200) DEFAULT NULL,
  `AccountVerified` int(1) NOT NULL DEFAULT '0',
  `VerifyHash` varchar(300) NOT NULL,
  `ResetPasswordHash` varchar(120) DEFAULT NULL,
  `ResetPasswordExpTime` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `Answer`
  ADD PRIMARY KEY (`AnswerID`);

--
-- Indexes for table `Assignment`
--
ALTER TABLE `Assignment`
  ADD PRIMARY KEY (`AssignmentID`);

--
-- Indexes for table `Course`
--
ALTER TABLE `Course`
  ADD PRIMARY KEY (`CourseID`);

--
-- Indexes for table `Question`
--
ALTER TABLE `Question`
  ADD PRIMARY KEY (`QuestionID`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Answer`
--
ALTER TABLE `Answer`
  MODIFY `AnswerID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `Assignment`
--
ALTER TABLE `Assignment`
  MODIFY `AssignmentID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `Course`
--
ALTER TABLE `Course`
  MODIFY `CourseID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `Question`
--
ALTER TABLE `Question`
  MODIFY `QuestionID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `UserID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;