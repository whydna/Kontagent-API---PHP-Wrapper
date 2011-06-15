<?php

require_once './kontagent.php';
require_once './facebook.php';

// init Kontagent
$ktApiKey = '<YOUR_KT_API_KEY>';
$ktSecretKey = '<YOUR_KT_SECRET_KEY>';
$useTestServer = true;
$kt = new Kontagent($ktApiKey, $ktSecretKey, $useTestServer);

// init Facebook
$fbAppId = '<YOUR_FB_APP_ID>';
$fbSecretKey = '<YOUR_FB_SECRET_KEY>';
$fb = new Facebook(array('appId' => $fbAppId, 'secret' => $fbSecretKey));

$fbUserId = $fb->getUser();

// after an invite is sent out, Facebook redirects the user to this page
// and appends the $_GET['request_ids'] parameter.
if (isset($_GET['request_ids'])) {
	foreach($_GET['request_ids'] as $requestId) {
		// retrieve the request data
		$request = json_decode(file_get_contents('https://graph.facebook.com/' . $requestId));
		$requestData = explode('|', $request['data']);

		// send InviteSent message to Kontagent
		$kt->trackInviteSent($fbUserId, $request['to']['id'], $requestData[0], $requestData[1], $requestData[2], $requestData[3]);
	}	
}

// redirect them back to your app
header("Location: index.php");

?>
