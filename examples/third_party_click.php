<?php

// The landing URL you give to your ad-provider should have some GET params appended to it.
// This will allow you to identify the type of ad that was clicked on your landing page.
// For example, the type (ad/partner) and subtype information.
// Your link will end up looking something like: 
// http://example.com/third_party_click.php?kt_type=ad&kt_st1=subtype1&kt_st2=subtype2&kt_st3=subtype3

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

// check for the presence of the Kontagent parameters
if (isset($_GET['kt_type'])) {
	$shortUniqueTrackingTag = $kt->genShortUniqueTrackingTag();
	
	$kt->trackThirdPartyCommClick(
		$_GET['kt_type'], 
		$shortUniqueTrackingTag,
		$fbUserId,
		$_GET['kt_st1'],
		$_GET['kt_st2'],
		$_GET['kt_st3']
	);

	// At this point we will want to prompt the user to install (see the Basics example).
	// If the user installs the app, we want to include the ShortUniqueTrackingTag
	// in the ApplicationAdded call (hint: you can embed ShortUniqueTrackingTag as a 
	// GET parameter in the callback URL). 
	// This will allow us to link the install back to this click.
}

?>
