<?php
	ini_set('include_path','.:include/:panels/:library/');
	include('header.php');
?>
	<div style="padding-left: 43px; padding-right: 30px;">
<?php
	include('search_panel.php');
?>

	</div>
	<nav class="topnav">
<?php
//	include('upload_panel.html');
	include('operations_panel.html');
?>
	</nav>
	<div id="result">
<?php
	include('welcome_panel.php');
	include('software_panel.html');
?>
	</div>
<?php
	include('footer.php');
?>
