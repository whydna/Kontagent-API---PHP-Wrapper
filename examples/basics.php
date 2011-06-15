<?php

// include the kontagent and facebook libraries
require_once './kontagent.php';
require_once './facebook.php';

// instantiate and configure kontagent
$ktApiKey = '<YOUR_KT_API_KEY>';
$ktSecretKey = '<YOUR_KT_SECRET_KEY>';
$useTestServer = true;
$kt = new Kontagent($ktApiKey, $ktSecretKey, $useTestServer);

// instantiate facebook lib
$fbAppId = '<YOUR_FB_APP_ID>';
$fbSecretKey = '<YOUR_FB_SECRET_KEY>';
$fb = new Facebook(array(
	'appId' => $fbAppId,
	'secret' => $fbSecretKey
));


// try to get the current facebook user
if ($fb->getSession()) {
	try {
		$fbUser = $facebook->api('/me');
	} catch (FacebookApiException $e) {
		header("Location: " . $fb->getLoginUrl());
	}
}

if (!$fbUser) {
	header("Location: " . $fb->getLoginUrl());
}

// track the page request
$kt->trackPageRequest($fbUser['id']);

// Facebook appends 'installed=1' to the URL
// if this is a new user adding your application.
if (isset($_GET['installed'])) {
	// track the install
	$kt->trackApplicationAdded($fbUser['id']);
	
	// track the user information
	$kt->trackUserInformation($fbUser['id'], null, $fbUser['gender']);
}

?>

<html>
	<head>
		<title>Kontagent Instrumentation Example: The Basics</title>
	</head>
	<body>
		<h1>Hello Kontagent!</h1>
	</body>
</html>

