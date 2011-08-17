<?php

require_once './kontagent_api.php';
require_once './facebook.php';

// init Kontagent
$ktApiKey = '<YOUR_KT_API_KEY>';
$useTestServer = true;
$ktApi = new KontagentApi($ktApiApiKey, array('useTestServer' => $useTestServer));

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
		$kt->trackInviteSent($fbUserId, $request['to']['id'], array(
			'uniqueTrackingTag' => $requestData[0], 
			'subtype1' => $requestData[1], 
			'subtype2' => $requestData[2], 
			'subtype3' => $requestData[3]
		));
	}	
}

// redirect them back to your app
header("Location: index.php");

?>
