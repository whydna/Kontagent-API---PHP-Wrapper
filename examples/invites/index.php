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

// Check if the current user is coming from an invite response.
// Facebook appends the $_GET['request_ids'] parameter when users enter via a request.
if (isset($_GET['request_ids'])) {
	foreach($_GET['request_ids'] as $requestId) {
		// retrieve the request data
		$request = json_decode(file_get_contents('https://graph.facebook.com/' . $requestId));
		list($uniqueTrackingTag, $subtype1, $subtype2, $subtype3) = explode('|', $request['data']);

		// send InviteResponse message to Kontagent
		$kt->trackInviteResponse($uniqueTrackingTag, $fbUserId, $subtype1, $subtype2, $subtype3);
	}

	// At this point we will want to prompt the user to install (see the Basics example).
	// If the user installs the app, we want to include the UniqueTrackingTag
	// in the ApplicationAdded call (hint: you can embed UniqueTrackingTag as a 
	// GET parameter in the callback URL). 
	// This will allow us to link the install back to this invite.
}

?>

<html>
	<head>
		<title>Kontagent Instrumentation Example: Invites</title>
	</head>
	<body>
		<h1>Hello Kontagent!</h1>
		
		<?php	
		// Generate the Facebook invite. Note the '$data' field where we embed some information we will need
		// to access later.
		$redirectUrl = 'invite_sent.php';
		$message = 'Check out my cool game!';
		$data = $kt->genUniqueTrackingTag() . '|subtype1|subtype2|subtype3';
		$inviteUrl = 'http://www.facebook.com/dialog/apprequests?app_id=' . $fbAppId 
			. '&redirect_uri=' . urlencode($redirectUrl)
			. '&message=' . $message
			. '&data=' . $data;
		?>
		<input type="button" value="Send Invite!" onclick="window.location='<?php echo $inviteUrl; ?>'"/>
	</body>
</html>


