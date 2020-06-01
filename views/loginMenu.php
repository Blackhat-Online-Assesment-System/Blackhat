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
						echo "<li class='nav-item'><a class='nav-link' href=\"login.php\">Login</a></li>";
						echo "<li class='nav-item'><a class='nav-link' href=\"signup.php\">Signup</a></li>";
					?>
				</ul>
	</div>
</nav>