<?php

// include the kontagent and facebook libraries
require_once './kontagent.php';
require_once './facebook.php';

// instantiate and configure kontagent
$ktApiKey ='your_kt_api_key';
$ktSecretKey = 'your_kt_secret_key';
$useTestServer = true;
$kt = new Kontagent($ktApiKey, $ktSecretKey, $useTestServer);

// instantiate facebook lib
$fbAppId = 'your_fb_app_id';
$fbSecretKey = 'your_fb_secret_key';
$fb = new Facebook(array(
	'appId' => $fbAppId,
	'secret' => $fbSecretKey
));

$fbUserId = $fb->getUser();

// check if an action was performed
if (isset($_POST['user_action'])) {
	// send the Event message to Kontagent
	$kt->trackEvent($fbUserId, $_POST['user_action'], null, null, 'subtype1', 'subtype2');
}

?>

<html>
	<head>
		<title></title>
	</head>
	<body>
		<form action="" method="post">
			<input type="radio" name="user_action" value="action_1"/> Action 1<br/>
			<input type="radio" name="user_action" value="action_2"/> Action 2<br/>
			<input type="radio" name="user_action" value="action_3"/> Action 3<br/>
			<input type="submit" name="submit" value="Perform Action"/>
		</form>
	</body>
</html>
