<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $company_name;?> Data Management</title>
<link rel="stylesheet" href="/admin/css/html5reset-1.6.1.css" type="text/css">
<link rel="stylesheet" href="/admin/css/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="/admin/css/smoothness/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="/admin/css/result.css" type="text/css">
<link rel="stylesheet" href="/admin/site-css/admin.css" type="text/css">
</head>
<body>
<div id="page">
	<nav>
	<div width="100%" align="right" style="height: 54px; background: url(/admin/lds3_images/banner.png) no-repeat 0% 0%; color: white;">
		<span class="sitetitle">
			<?php echo $company_name . " Data"; ?>
		</span>
		<?php
			if ($_SESSION["user_pic"]) {
				echo '<img src="' . $_SESSION["user_pic"] . '" style="float: right;" height="40px" border="0"/>';
			}
			if ($_SESSION["user_name"]) {
				echo '<span style="float:right; padding-top: 10px;">' . $_SESSION["user_name"] . '&nbsp;(<a href="glogin.php?logout">Logout</a>)&nbsp;&nbsp;</span>';
			}
		?>	
	</div>
	<section class="formats"><ul>
<li class="first"><a href="/" type="text/html" title="<?php echo $company_name;?> Data"><?php echo $company_name; ?> Data</a></li>
<li><a href="/sparql/" type="text/html" title="SPARQL">SPARQL Endpoint</a></li>
<li><a href="<?php echo $company_homepage;?>" type="text/html" title="<?php echo $company_name;?> Home"><?php echo $company_name;?> Home</a></li>
<li class="last"><a href="<?php echo $company_homepage;?>/contact" type="text/html" title="Contact <?php echo $company_name;?>">Contact</a></li>
</ul></section>
	</nav>	
	<header>
	<h1><a href="/admin/">Linked Data Admin</a></h1>
	</header>
<?php
	include('messages.php');
	include_once('config.php');
?>
