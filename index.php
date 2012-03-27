<?php
	ini_set('include_path','.:include/:panels/:library/');
	include('header.php');
?>
	<nav class="topnav">
		<section class="view">
			<h1>Login</h1>
			<br/><span style="margin-left: 2em;"><b>Login Using:</b><br/><br/></span>
			<div align="center">	
				<a href="glogin.php?signin"><img width="75%" src="/admin/lds3_images/google_logo_33.png" border="0"/></a>
			</div>
		</section>
	</nav>
	<div id="result">
<?php
	include('welcome_panel.php');
?>
	</div>
<?php
	include('footer.php');
?>
