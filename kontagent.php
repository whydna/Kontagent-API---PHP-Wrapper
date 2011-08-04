<?php

class Kontagent
{
	private $baseApiUrl = "http://api.geo.kontagent.net/api/v1/";
	private $baseTestServerUrl = "http://test-server.kontagent.net/api/v1/";
	
	private $apiKey = null;
	private $apiSecret = null;
	private $validateParams = null;
	private $useTestServer = null;
	
	private $useCurl = null;

	/*
	* Kontagent class constructor
	*
	* @param string $apiKey The app's Kontagent API key
	* @param string $apiSecret The app's Kontagent secret key
	* @param bool $useTestServer Whether to send messages to the Kontagent Test Server
	* @param bool $validateParams Whether to validate the parameters passed into the tracking methods
	*/
	public function __construct($apiKey, $apiSecret, $useTestServer = false, $validateParams = false)
	{
		$this->apiKey = $apiKey;
		$this->apiSecret = $apiSecret;
		$this->useTestServer = $useTestServer;
		$this->validateParams = $validateParams;
		
		// determine whether curl is installed on the server
		$this->useCurl = (function_exists('curl_init')) ? true : false;
	}

	/*
	* Sends the API message.
	*
	* @param string $messageType The message type to send ('apa', 'ins', etc.)
	* @param array $params An associative array containing paramName => value (ex: 's'=>123456789)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	private function sendMessage($messageType, Array $params, &$errorMessage = null)
	{
		if ($this->validateParams) {
			// validate the message parameters
			$errorMessage = null;
			
			foreach($params as $paramName => $paramValue) {
				if (!KtValidator::validateParameter($messageType, $paramName, $paramValue, $errorMessage)) {
					return false;
				}
			}
		}
	
		// generate URL of the API request
		$url = null;
		
		if ($this->useTestServer) {
			$url = $this->baseTestServerUrl . $this->apiKey . "/" . $messageType . "/?" . http_build_query($params, '', '&');
		} else {
			$url = $this->baseApiUrl . $this->apiKey . "/" . $messageType . "/?" . http_build_query($params, '', '&');
		}
		
		// use curl if available, otherwise use file_get_contents() to send the request
		if ($this->useCurl) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
		} else {
			file_get_contents($url);
		}

		return true;
	}
	
	/*
	* Generates a unique tracking tag.
	*
	* @return string The unique tracking tag
	*/
	public function genUniqueTrackingTag()
	{
		$uniqueTrackingTag = uniqid() . uniqid();
		return substr($uniqueTrackingTag, -16);
	}
	
	/*
	* Generates a short unique tracking tag.
	*
	* @return string The short unique tracking tag
	*/
	public function genShortUniqueTrackingTag()
	{
		$shortUniqueTrackingTag = uniqid();
		return substr($shortUniqueTrackingTag, -8);
	}
	
	/*
	* Sends an Invite Sent message to Kontagent.
	*
	* @param string $userId The UID of the sending user
	* @param string $recipientUserIds A comma-separated list of the recipient UIDs
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	InviteSent->InviteResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackInviteSent($userId, $recipientUserIds, $uniqueTrackingTag, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{	
		$params = array(
			's' => $userId,
			'r' => $recipientUserIds,
			'u' => $uniqueTrackingTag
		);
		
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
			
		return $this->sendMessage("ins", $params, $errorMessage);
	}
	
	/*
	* Sends an Invite Response message to Kontagent.
	*
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	InviteSent->InviteResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $recipientUserId The UID of the responding user
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackInviteResponse($uniqueTrackingTag, $recipientUserId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			'i' => 0,
			'u' => $uniqueTrackingTag
		);
		
		if ($recipientUserId) { $params['r'] = $recipientUserId; }
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
	
		return $this->sendMessage("inr", $params, $errorMessage);
	}
	
	/*
	* Sends an Notification Sent message to Kontagent.
	*
	* @param string $userId The UID of the sending user
	* @param string $recipientUserIds A comma-separated list of the recipient UIDs
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationSent->NotificationResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackNotificationSent($userId, $recipientUserIds, $uniqueTrackingTag, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			's' => $userId,
			'r' => $recipientUserIds,
			'u' => $uniqueTrackingTag
		);
		
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
		
		return $this->sendMessage("nts", $params, $errorMessage);
	}

	/*
	* Sends an Notification Response message to Kontagent.
	*
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationSent->NotificationResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $recipientUserId The UID of the responding user
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackNotificationResponse($uniqueTrackingTag, $recipientUserId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			'i' => 0,
			'u' => $uniqueTrackingTag
		);
		
		if ($recipientUserId) { $params['r'] = $recipientUserId; }
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
	
		return $this->sendMessage("ntr", $params, $errorMessage);
	}
	
	/*
	* Sends an Notification Email Sent message to Kontagent.
	*
	* @param string $userId The UID of the sending user
	* @param string $recipientUserIds A comma-separated list of the recipient UIDs
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackNotificationEmailSent($userId, $recipientUserIds, $uniqueTrackingTag, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			's' => $userId,
			'r' => $recipientUserIds,
			'u' => $uniqueTrackingTag
		);
		
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
	
		return $this->sendMessage("nes", $params, $errorMessage);
	}

	/*
	* Sends an Notification Email Response message to Kontagent.
	*
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $recipientUserId The UID of the responding user
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackNotificationEmailResponse($uniqueTrackingTag, $recipientUserId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			'i' => 0,
			'u' => $uniqueTrackingTag
		);
		
		if ($recipientUserId) { $params['r'] = $recipientUserId; }
		if ($subtype1) { $params['st1'] = $subtype1; }	
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
	
		return $this->sendMessage("nei", $params, $errorMessage);
	}

	/*
	* Sends an Stream Post message to Kontagent.
	*
	* @param string $userId The UID of the sending user
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $type The Facebook channel type
	*	(feedpub, stream, feedstory, multifeedstory, dashboard_activity, or dashboard_globalnews).
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackStreamPost($userId, $uniqueTrackingTag, $type, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			's' => $userId,
			'u' => $uniqueTrackingTag,
			'tu' => $type
		);
		
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
		
		return $this->sendMessage("pst", $params, $errorMessage);
	}

	/*
	* Sends an Stream Post Response message to Kontagent.
	*
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $type The Facebook channel type
	*	(feedpub, stream, feedstory, multifeedstory, dashboard_activity, or dashboard_globalnews).
	* @param string $recipientUserId The UID of the responding user
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackStreamPostResponse($uniqueTrackingTag, $type, $recipientUserId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			'i' => 0,
			'u' => $uniqueTrackingTag,
			'tu' => $type
		);
		
		if ($recipientUserId) { $params['r'] = $recipientUserId; }
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
	
		return $this->sendMessage("psr", $params, $errorMessage);
	}

	/*
	* Sends an Custom Event message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param string $eventName The name of the event
	* @param int $value A value associated with the event
	* @param int $level A level associated with the event (must be positive)
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackEvent($userId, $eventName, $value = null, $level = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			's' => $userId,
			'n' => $eventName
		);
		
		if ($value) { $params['v'] = $value; }
		if ($level) { $params['l'] = $level; }
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
	
		return $this->sendMessage("evt", $params, $errorMessage);
	}

	/*
	* Sends an Application Added message to Kontagent.
	*
	* @param string $userId The UID of the installing user
	* @param string $uniqueTrackingTag 16-digit hex string used to match 
	*	Invite/StreamPost/NotificationSent/NotificationEmailSent->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $shortUniqueTrackingTag 8-digit hex string used to match 
	*	ThirdPartyCommClicks->ApplicationAdded messages. 
	*	See the genShortUniqueTrackingTag() helper method.
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackApplicationAdded($userId, $uniqueTrackingTag = null, $shortUniqueTrackingTag = null, &$errorMessage = null)
	{
		$params = array('s' => $userId);
		
		if ($uniqueTrackingTag) { $params['u'] = $uniqueTrackingTag; }
		if ($shortUniqueTrackingTag) { $params['su'] = $shortUniqueTrackingTag; }
	
		return $this->sendMessage("apa", $params, $errorMessage);
	}

	/*
	* Sends an Application Removed message to Kontagent.
	*
	* @param string $userId The UID of the removing user
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackApplicationRemoved($userId, &$errorMessage = null)
	{
		$params = array('s' => $userId);
	
		return $this->sendMessage("apr", $params, $errorMessage);
	}
	
	/*
	* Sends an Third Party Communication Click message to Kontagent.
	*
	* @param string $type The third party comm click type (ad, partner).
	* @param string $shortUniqueTrackingTag 8-digit hex string used to match 
	*	ThirdPartyCommClicks->ApplicationAdded messages. 
	* @param string $userId The UID of the user
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackThirdPartyCommClick($type, $shortUniqueTrackingTag, $userId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			'i' => 0,
			'tu' => $type
		);
		
		if ($shortUniqueTrackingTag) { $params['su'] = $shortUniqueTrackingTag; }
		if ($userId) { $params['s'] = $userId; }
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }	
	
		return $this->sendMessage("ucc", $params, $errorMessage);
	}

	/*
	* Sends an Page Request message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param int $ipAddress The current users IP address
	* @param string $pageAddress The current page address (ex: index.html)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackPageRequest($userId, $ipAddress = null, $pageAddress = null, &$errorMessage = null)
	{
		$params = array(
			's' => $userId,
			'ts' => time() 
		);
		
		if ($ipAddress) { $params['ip'] = $ipAddress; }
		if ($pageAddress) { $params['u'] = $pageAddress; }
	
		return $this->sendMessage("pgr", $params, $errorMessage);
	}

	/*
	* Sends an User Information message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param int $birthYear The birth year of the user
	* @param string $gender The gender of the user (m,f,u)
	* @param string $country The 2-character country code of the user
	* @param int $friendCount The friend count of the user
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackUserInformation($userId, $birthYear = null, $gender = null, $country = null, $friendCount = null, &$errorMessage = null)
	{
		$params = array('s' => $userId);
		
		if ($birthYear) { $params['b'] = $birthYear; }
		if ($gender) { $params['g'] = $gender; }
		if ($country) { $params['lc'] = strtoupper($country); }
		if ($friendCount) { $params['f'] = $friendCount; }

		return $this->sendMessage("cpu", $params, $errorMessage);
	}

	/*
	* Sends an Goal Count message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param int $goalCount1 The amount to increment goal count 1 by
	* @param int $goalCount2 The amount to increment goal count 2 by
	* @param int $goalCount3 The amount to increment goal count 3 by
	* @param int $goalCount4 The amount to increment goal count 4 by
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackGoalCount($userId, $goalCount1 = null, $goalCount2 = null, $goalCount3 = null, $goalCount4 = null, &$errorMessage = null)
	{
		$params = array('s' => $userId);
		
		if ($goalCount1) { $params['gc1'] = $goalCount1; }
		if ($goalCount2) { $params['gc2'] = $goalCount2; }
		if ($goalCount3) { $params['gc3'] = $goalCount3; }
		if ($goalCount4) { $params['gc4'] = $goalCount4; }
	
		return $this->sendMessage("gci", $params, $errorMessage);
	}

	/*
	* Sends an Revenue message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param int $value The amount of revenue in cents
	* @param string $type The transaction type (direct, indirect, advertisement, credits, other)
	* @param string $subtype1 Subtype1 value (max 32 chars)
	* @param string $subtype2 Subtype2 value (max 32 chars)
	* @param string $subtype3 Subtype3 value (max 32 chars)
	* @param string $errorMessage The error message on failure
	* 
	* @return bool Returns true on success, false otherwise
	*/
	public function trackRevenue($userId, $value, $type = null,  $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)
	{
		$params = array(
			's' => $userId,
			'v' => $value
		);
		
		if ($type) { $params['tu'] = $type; }
		if ($subtype1) { $params['st1'] = $subtype1; }
		if ($subtype2) { $params['st2'] = $subtype2; }
		if ($subtype3) { $params['st3'] = $subtype3; }
	
		return $this->sendMessage("mtu", $params, $errorMessage);
	}
}

/*
* Helper class to validate the paramters for the Kontagent API messages
*/
class KtValidator
{
	/*
	* Validates a parameter of a given message type.
	*
	* @param string $messageType The message type that the param belongs to (ex: ins, apa, etc.)
	* @param string $paramName The name of the parameter (ex: s, su, u, etc.)
	* @param mixed $paramValue The value of the parameter
	* @param string $errorMessage If the parameter value is invalid, this will be populated with the error message
	*
	* @returns bool Returns true on success and false on failure.
	*/
	public static function validateParameter($messageType, $paramName, $paramValue, &$errorMessage = null) 
	{
		// generate name of the dynamic method
		$methodName = 'validate' . ucfirst($paramName);
		
		if (!self::$methodName($messageType, $paramValue, $errorMessage)) {
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateB($messageType, $paramValue, &$errorMessage = null)
	{
		// birthyear param (cpu message)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1900, 'max_range' => 2011)))) {
			$errorMessage = 'Invalid birth year.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateF($messageType, $paramValue, &$errorMessage = null)
	{
		// friend count param (cpu message)
		if(!filter_var($paramValue, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
			$errorMessage = 'Invalid friend count.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateG($messageType, $paramValue, &$errorMessage = null)
	{
		// gender param (cpu message)
		if (preg_match('/^[mfu]$/', $paramValue) == 0) {
			$errorMessage = 'Invalid gender.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateGc1($messageType, $paramValue, &$errorMessage = null)
	{
		// goal count param (gc1, gc2, gc3, gc4 messages)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT, array('options' => array('min_range' => -16384, 'max_range' => 16384)))) {
			$errorMessage = 'Invalid goal count value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateGc2($messageType, $paramValue, &$errorMessage = null)
	{
		return self::validateGc1($messageType, $paramValue, $errorMessage);
	}
	
	private static function validateGc3($messageType, $paramValue, &$errorMessage = null)
	{
		return self::validateGc1($messageType, $paramValue, $errorMessage);
	}
	
	private static function validateGc4($messageType, $paramValue, &$errorMessage = null)
	{
		return self::validateGc1($messageType, $paramValue, $errorMessage);
	}
	
	private static function validateI($messageType, $paramValue, &$errorMessage = null)
	{
		// isAppInstalled param (inr, psr, ner, nei messages)
		if (preg_match('/^[01]$/', $paramValue) == 0) {
			$errorMessage = 'Invalid isAppInstalled value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateIp($messageType, $paramValue, &$errorMessage = null)
	{
		// ip param (pgr messages)
		if (!filter_var($paramValue, FILTER_VALIDATE_IP)) {
			$errorMessage = 'Invalid ip address value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateL($messageType, $paramValue, &$errorMessage = null)
	{
		// level param (evt messages)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
			$errorMessage = 'Invalid level value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateLc($messageType, $paramValue, &$errorMessage = null)
	{
		// country param (cpu messages)
		if (preg_match('/^[A-Z]{2}$/', $paramValue) == 0) {
			$errorMessage = 'Invalid country value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateLp($messageType, $paramValue, &$errorMessage = null)
	{
		// postal/zip code param (cpu messages)
		// this parameter isn't being used so we just return true for now
		return true;
	}
	
	private static function validateLs($messageType, $paramValue, &$errorMessage = null)
	{
		// state param (cpu messages)
		// this parameter isn't being used so we just return true for now
		return true;
	}
	
	private static function validateN($messageType, $paramValue, &$errorMessage = null)
	{
		// event name param (evt messages)
		if (preg_match('/^[A-Za-z0-9-_]{1,32}$/', $paramValue) == 0) {
			$errorMessage = 'Invalid event name value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateR($messageType, $paramValue, &$errorMessage = null)
	{
		// Sending messages include multiple recipients (comma separated) and
		// response messages can only contain 1 recipient UID.
		if ($messageType == 'ins' || $messageType == 'nes' || $messageType == 'nts') {
			// recipients param (ins, nes, nts messages)
			if (preg_match('/^[0-9]+(,[0-9]+)*$/', $paramValue) == 0) {
				$errorMessage = 'Invalid recipient user ids.';
				return false;
			}
		} elseif ($messageType == 'inr' || $messageType == 'psr' || $messageType == 'nei' || $messageType == 'ntr') {
			// recipient param (inr, psr, nei, ntr messages)
			if (!filter_var($paramValue, FILTER_VALIDATE_INT)) {
				$errorMessage = 'Invalid recipient user id.';
				return false;
			}
		}
	
		return true;
	}
	
	private static function validateS($messageType, $paramValue, &$errorMessage = null)
	{
		// userId param
		if (!filter_var($paramValue, FILTER_VALIDATE_INT)) {
			$errorMessage = 'Invalid user id.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateSt1($messageType, $paramValue, &$errorMessage = null)
	{
		// subtype1 param
		if (preg_match('/^[A-Za-z0-9-_]{1,32}$/', $paramValue) == 0) {
			$errorMessage = 'Invalid subtype value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateSt2($messageType, $paramValue, &$errorMessage = null)
	{
		return self::validateSt1($messageType, $paramValue, $errorMessage);
	}
	
	private static function validateSt3($messageType, $paramValue, &$errorMessage = null)
	{
		return self::validateSt1($messageType, $paramValue, $errorMessage);
	}

	private static function validateSu($messageType, $paramValue, &$errorMessage = null)
	{
		// short tracking tag param
		if (preg_match('/^[A-Fa-f0-9]{8}$/', $paramValue) == 0) {
			$errorMessage = 'Invalid short unique tracking tag.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateTs($messageType, $paramValue, &$errorMessage = null)
	{
		// timestamp param (pgr message)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT)) {
			$errorMessage = 'Invalid timestamp.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateTu($messageType, $paramValue, &$errorMessage = null)
	{
		// type parameter (mtu, pst/psr, ucc messages)
		// acceptable values for this parameter depends on the message type
		if ($messageType == 'mtu') {
			if (preg_match('/^(direct|indirect|advertisement|credits|other)$/', $paramValue) == 0) {
				$errorMessage = 'Invalid monetization type.';
				return false;
			}
		} elseif ($messageType == 'pst' || $messageType == 'psr') {
			if (preg_match('/^(feedpub|stream|feedstory|multifeedstory|dashboard_activity|dashboard_globalnews)$/', $paramValue) == 0) {
				$errorMessage = 'Invalid stream post/response type.';
				return false;
			}
		} elseif ($messageType == 'ucc') {
			if (preg_match('/^(ad|partner)$/', $paramValue) == 0) {
				$errorMessage = 'Invalid third party communication click type.';
				return false;
			}
		}
		
		return true;
	}
	
	private static function validateU($messageType, $paramValue, &$errorMessage = null)
	{
		// unique tracking tag parameter for all messages EXCEPT pgr.
		// for pgr messages, this is the "page address" param
		if ($messageType != 'pgr') {
			if (preg_match('/^[A-Fa-f0-9]{32}$/', $paramValue) == 0) {
				$errorMessage = 'Invalid unique tracking tag.';
				return false;
			}
		}
		
		return true;
	}
	
	private static function validateV($messageType, $paramValue, &$errorMessage = null)
	{
		// value param (mtu, evt messages)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT)) {
			$errorMessage = 'Invalid value.';
			return false;
		} else {
			return true;
		}
	}
}
