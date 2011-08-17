<?php

// include the kontagent and facebook libraries
require_once './kontagent_api.php';
require_once './facebook.php';

// instantiate and configure kontagent
$ktApiKey = '<YOUR_KT_API_KEY>';
$useTestServer = true;
$ktApi = new KontagentApi($ktApiApiKey, array('useTestServer' => $useTestServer));

// instantiate facebook lib
$fbAppId = '<YOUR_FB_APP_ID>';
$fbSecretKey = '<YOUR_FB_SECRET_KEY>';
$fb = new Facebook(array(
	'appId' => $fbAppId,
	'secret' => $fbSecretKey
));

$fbUserId = $fb->getUser();

// check if an action was performed
if (isset($_POST['user_action'])) {
	// send the Event message to Kontagent
	$ktApi->trackEvent($fbUserId, $_POST['user_action'], array('subtype1' => 'st1', 'subtype2' => 'st1'));
}

?>

<html>
	<head>
		<title>Kontagent Instrumentation Example: Events</title>
	</head>
	<body>
		<h1>Hello Kontagent!</h1>

		<form action="" method="post">
			<input type="radio" name="user_action" value="action_1"/> Action 1<br/>
			<input type="radio" name="user_action" value="action_2"/> Action 2<br/>
			<input type="radio" name="user_action" value="action_3"/> Action 3<br/>
			<input type="submit" name="submit" value="Perform Action"/>
		</form>
	</body>
</html>
