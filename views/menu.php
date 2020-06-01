<?php
/********************************
* Project: Blackhat - Online Assesment System
* Code Version: 1.2.0
* Github: https://github.com/Blackhat-Online-Assesment-System/Blackhat
* Author: @sommerbenjamin
* Author: @shmuelhalbfinger
***************************************************************************************/

/**
* @File views/menu.php
* Make top menu bar for the website to include on all pages for the menu
*/

require_once('functions.php');

?>
<nav class="navbar navbar-expand-lg navbar navbar-dark bg-dark">
  <div class="collapse navbar-collapse" id="navbarColor02">
	<ul class="navbar-nav ml-auto">
		<?php 
			/* Here we output the menu items specific to a student who is logged in, this also outputs if no one is logged in at all */
			if(!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] == null || $_SESSION['UserRole'] == "student"){
		?>
			<li class="nav-item"><a class="nav-link" href="assignments.php">Assignments</a></li>
			<li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
			<li class="nav-item"><a class="nav-link" href="grades.php">Grades</a></li>
		<?php
			/* Otherwise output the Instructor Menu */
			} else if($_SESSION['UserRole'] == "instructor"){
		?>
			<li class="nav-item"><a class="nav-link" href="instructorAssignments.php">Assignments</a></li>
			<li class="nav-item"><a class="nav-link" href="instructorCourses.php">Courses</a></li>
			<li class="nav-item"><a class="nav-link" href="instructorGrades.php">Grades</a></li>
		<?php
			}
		?>
			<li class="nav-item"><a class="nav-link" href="userSettings.php">User Preferences</a></li>
		<?php
			/* Lastly, show the Logout or Login/Signup pages if the user is logged in or not */
			if(CheckUserAuthentication()){
				echo "<li class='nav-item'><a class='nav-link' href=\"logout.php\">Logout</a></li>";
			} else {
				echo "<li class='nav-item'><a class='nav-link' href=\"login.php\">Login</a></li>";
				echo "<li class='nav-item'><a class='nav-link' href=\"signup.php\">Signup</a></li>";
			}
		?>
		
	</ul>
  </div>
</nav>