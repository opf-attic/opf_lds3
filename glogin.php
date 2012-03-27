<?php

# Arvin Castro, arvin@sudocode.net
# 16 June 2011
# http://sudocode.net/article/430/get-a-users-google-email-address-via-oauth2-in-php

session_name('googleoauth2');
session_start();

require 'class-xhttp-php/class.xhttp.php';
include_once('config.php');

# http://code.google.com/apis/console#access
$redirect_uri = 'http://'. $_SERVER["SERVER_NAME"].'/admin/glogin.php';

# Scope for getting the user's email address https://sites.google.com/site/oauthgoog/Home/emaildisplayscope
$scope = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';

if(isset($_GET['signin'])) {

	# STEP 2:
	# Build URL for OAuth2 authorization
	$url = "https://accounts.google.com/o/oauth2/auth?".http_build_query(array(
		'client_id' => $google_client_id,
		'redirect_uri' => $redirect_uri,
		'scope' => $scope,
		'response_type' => 'code'
	));

	# STEP 3:
	# Redirect user to URL for authorization;
	header('Location: '.$url, true, 302);
	die();

} elseif(isset($_GET['code'])) {

	# STEP 4:
	# User granted access to us; User is redirected back to our application; code parameter is included

	# STEP 5:
	# Exchange code for access token and secret
	$data = array('post' => array(
		'code' => $_GET['code'],
		'client_id' => $google_client_id,
		'client_secret' => $google_client_secret,
		'redirect_uri' => $redirect_uri,
		'grant_type' => 'authorization_code',
	));
	$response = xhttp::fetch('https://accounts.google.com/o/oauth2/token', $data);

	if($response['successful']) {

		# STEP 6:
		# We got the access token; User is now logged in
		$_SESSION['loggedin'] = true;
		$_SESSION = array_merge($_SESSION, $response['json']);

		# Redirect user to remove code parameter in URL, Optional
		header('Location: '.$redirect_uri);
		die();

	} else {
		# STEP 6: Alternate
		# Unable to get access token; repeat STEP 5 or give up
	}

} elseif(isset($_GET['error'])) {

	# STEP 4: Alternate
	# User refused to give access to his email address; Ask feedback, optional; Repeat STEP 1

} elseif(isset($_GET['logout'])) {

	# STEP 10:
	# Log out of session; delete cookies
	$_SESSION = array();
	session_destroy();
	setcookie(session_name(), null, time() - 3600);
	header('Location: index.php');
}

if($_SESSION['loggedin']) {
	# STEP 7:
	# Retrieve user's email; Pass access token via the Authorization header field
	$response1 = xhttp::fetch('https://www.googleapis.com/oauth2/v1/userinfo?alt=json', array(
		'headers' => array(
			'Authorization' => "OAuth $_SESSION[access_token]"
		)));
	$response2 = xhttp::fetch('https://www.googleapis.com/userinfo/email?alt=json', array(
		'headers' => array(
			'Authorization' => "OAuth $_SESSION[access_token]"
		)));

	if($response1['successful']) {
		# STEP 8:
		# We got the user's email
		$_SESSION['user_name'] = $response1['json']['name'];
		$_SESSION['user_pic'] = $response1['json']['picture'];
		$_SESSION['user_email'] = $response2['json']['data']['email'];
	} else {
		# STEP 8: Alternate
		# Error getting user's email; repeat STEP 7 or Refresh token (not included) or repeat STEP 2 or repeat STEP 1
		echo $response['body'];
	}

	header('Location: main.php');
	# STEP 9:
	# Provide logout link to discard session

} else {
	# STEP 1: Provide link to user to Sign in with Google
	#echo '<a href="?signin">Sign in with Google</a>.';
	header('Location: index.php');
}

?>
