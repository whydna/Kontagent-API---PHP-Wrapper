<?php

class KontagentApi {
	private $baseApiUrl = "http://api.geo.kontagent.net/api/v1/";
	private $baseTestServerUrl = "http://test-server.kontagent.com/api/v1/";
	
	private $apiKey = null;
	private $validateParams = null;

	private $useTestServer = null;
	
	private $useCurl = null;

	/*
	* Kontagent class constructor
	*
	* @param string $apiKey The app's Kontagent API key
	* @param array $optionalParams An associative array containing paramName => value
	* @param bool $optionalParams['useTestServer'] Whether to send messages to the Kontagent Test Server
	* @param bool $optionalParams['validateParams'] Whether to validate the parameters passed into the tracking methods
	*/
	public function __construct($apiKey, $optionalParams = array()) {
		$this->apiKey = $apiKey;
		$this->useTestServer = ($optionalParams['useTestServer']) ? $optionalParams['useTestServer'] : false;
		$this->validateParams = ($optionalParams['validateParams']) ? $optionalParams['validateParams'] : false;
		
		// determine whether curl is installed on the server
		$this->useCurl = (function_exists('curl_init')) ? true : false;
	}

	/*
	* Sends an HTTP request given a URL
	*
	* @param string $url The message type to send ('apa', 'ins', etc.)
	*/
	public function sendHttpRequest($url) {
		// use curl if available, otherwise use file_get_contents() to send the request
		if ($this->useCurl) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
		} else {
			file_get_contents($url);
		}
	}

	/*
	* Sends the API message.
	*
	* @param string $messageType The message type to send ('apa', 'ins', etc.)
	* @param array $params An associative array containing paramName => value (ex: 's'=>123456789)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function sendMessage($messageType, $params, &$validationErrorMsg = null) {
		if ($this->validateParams) {
			// validate the message parameters
			$validationErrorMsg = null;
			
			foreach($params as $paramName => $paramValue) {
				if (!KtValidator::validateParameter($messageType, $paramName, $paramValue, $validationErrorMsg)) {
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
		
		$this->sendHttpRequest($url);

		return true;
	}
	
	/*
	* Generates a unique tracking tag.
	*
	* @return string The unique tracking tag
	*/
	public function genUniqueTrackingTag() {
		return substr(md5(uniqid(rand(), true)), -16);
	}
	
	/*
	* Generates a short unique tracking tag.
	*
	* @return string The short unique tracking tag
	*/
	public function genShortUniqueTrackingTag() {
		return substr(md5(uniqid(rand(), true)), -8);
	}
	
	/*
	* Sends an Invite Sent message to Kontagent.
	*
	* @param string $userId The UID of the sending user
	* @param string $recipientUserIds A comma-separated list of the recipient UIDs
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	InviteSent->InviteResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackInviteSent($userId, $recipientUserIds, $uniqueTrackingTag, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			's' => $userId,
			'r' => $recipientUserIds,
			'u' => $uniqueTrackingTag
		);
		
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
			
		return $this->sendMessage("ins", $params, $validationErrorMsg);
	}
	
	/*
	* Sends an Invite Response message to Kontagent.
	*
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	InviteSent->InviteResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['recipientUserId'] The UID of the responding user
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackInviteResponse($uniqueTrackingTag, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			'i' => 0,
			'u' => $uniqueTrackingTag
		);
		
		if (isset($optionalParams['recipientUserId'])) { $params['r'] = $optionalParams['recipientUserId']; }
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
	
		return $this->sendMessage("inr", $params, $validationErrorMsg);
	}
	
	/*
	* Sends an Notification Sent message to Kontagent.
	*
	* @param string $userId The UID of the sending user
	* @param string $recipientUserIds A comma-separated list of the recipient UIDs
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationSent->NotificationResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackNotificationSent($userId, $recipientUserIds, $uniqueTrackingTag, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			's' => $userId,
			'r' => $recipientUserIds,
			'u' => $uniqueTrackingTag
		);
		
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
		
		return $this->sendMessage("nts", $params, $validationErrorMsg);
	}

	/*
	* Sends an Notification Response message to Kontagent.
	*
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationSent->NotificationResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['recipientUserId'] The UID of the responding user
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackNotificationResponse($uniqueTrackingTag, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			'i' => 0,
			'u' => $uniqueTrackingTag
		);
		
		if (isset($optionalParams['recipientUserId'])) { $params['r'] = $optionalParams['recipientUserId']; }
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
	
		return $this->sendMessage("ntr", $params, $validationErrorMsg);
	}
	
	/*
	* Sends an Notification Email Sent message to Kontagent.
	*
	* @param string $userId The UID of the sending user
	* @param string $recipientUserIds A comma-separated list of the recipient UIDs
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackNotificationEmailSent($userId, $recipientUserIds, $uniqueTrackingTag, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			's' => $userId,
			'r' => $recipientUserIds,
			'u' => $uniqueTrackingTag
		);
		
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
	
		return $this->sendMessage("nes", $params, $validationErrorMsg);
	}

	/*
	* Sends an Notification Email Response message to Kontagent.
	*
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['recipientUserId'] The UID of the responding user
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackNotificationEmailResponse($uniqueTrackingTag, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			'i' => 0,
			'u' => $uniqueTrackingTag
		);
		
		if (isset($optionalParams['recipientUserId'])) { $params['r'] = $optionalParams['recipientUserId']; }
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }	
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
	
		return $this->sendMessage("nei", $params, $validationErrorMsg);
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
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackStreamPost($userId, $uniqueTrackingTag, $type, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			's' => $userId,
			'u' => $uniqueTrackingTag,
			'tu' => $type
		);
		
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
		
		return $this->sendMessage("pst", $params, $validationErrorMsg);
	}

	/*
	* Sends an Stream Post Response message to Kontagent.
	*
	* @param string $uniqueTrackingTag 32-digit hex string used to match 
	*	NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $type The Facebook channel type
	*	(feedpub, stream, feedstory, multifeedstory, dashboard_activity, or dashboard_globalnews).
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['recipientUserId'] The UID of the responding user
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackStreamPostResponse($uniqueTrackingTag, $type, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			'i' => 0,
			'u' => $uniqueTrackingTag,
			'tu' => $type
		);
		
		if (isset($optionalParams['recipientUserId'])) { $params['r'] = $optionalParams['recipientUserId']; }
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
	
		return $this->sendMessage("psr", $params, $validationErrorMsg);
	}

	/*
	* Sends an Custom Event message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param string $eventName The name of the event
	* @param array $optionalParams An associative array containing paramName => value
	* @param int $optionalParams['value'] A value associated with the event
	* @param int $optionalParams['level'] A level associated with the event (must be positive)
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackEvent($userId, $eventName, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			's' => $userId,
			'n' => $eventName
		);
		
		if (isset($optionalParams['value'])) { $params['v'] = $optionalParams['value']; }
		if (isset($optionalParams['level'])) { $params['l'] = $optionalParams['level']; }
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
	
		return $this->sendMessage("evt", $params, $validationErrorMsg);
	}

	/*
	* Sends an Application Added message to Kontagent.
	*
	* @param string $userId The UID of the installing user
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['uniqueTrackingTag'] 16-digit hex string used to match 
	*	Invite/StreamPost/NotificationSent/NotificationEmailSent->ApplicationAdded messages. 
	*	See the genUniqueTrackingTag() helper method.
	* @param string $optionalParams['shortUniqueTrackingTag'] 8-digit hex string used to match 
	*	ThirdPartyCommClicks->ApplicationAdded messages. 
	*	See the genShortUniqueTrackingTag() helper method.
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackApplicationAdded($userId, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array('s' => $userId);
		
		if (isset($optionalParams['uniqueTrackingTag'])) { $params['u'] = $optionalParams['uniqueTrackingTag']; }
		if (isset($optionalParams['shortUniqueTrackingTag'])) { $params['su'] = $optionalParams['shortUniqueTrackingTag']; }
	
		return $this->sendMessage("apa", $params, $validationErrorMsg);
	}

	/*
	* Sends an Application Removed message to Kontagent.
	*
	* @param string $userId The UID of the removing user
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackApplicationRemoved($userId, &$validationErrorMsg = null) {
		$params = array('s' => $userId);
	
		return $this->sendMessage("apr", $params, $validationErrorMsg);
	}
	
	/*
	* Sends an Third Party Communication Click message to Kontagent.
	*
	* @param string $type The third party comm click type (ad, partner).
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['shortUniqueTrackingTag'] 8-digit hex string used to match 
	*	ThirdPartyCommClicks->ApplicationAdded messages. 
	* @param string $optionalParams['userId'] The UID of the user
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackThirdPartyCommClick($type, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			'i' => 0,
			'tu' => $type
		);
		
		if (isset($optionalParams['shortUniqueTrackingTag'])) { $params['su'] = $optionalParams['shortUniqueTrackingTag']; }
		if (isset($optionalParams['userId'])) { $params['s'] = $optionalParams['userId']; }
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }	
	
		return $this->sendMessage("ucc", $params, $validationErrorMsg);
	}

	/*
	* Sends an Page Request message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param array $optionalParams An associative array containing paramName => value
	* @param int $optionalParams['ipAddress'] The current users IP address
	* @param string $optionalParams['pageAddress'] The current page address (ex: index.html)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackPageRequest($userId, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			's' => $userId,
			'ts' => time() 
		);
		
		if (isset($optionalParams['ipAddress'])) { $params['ip'] = $optionalParams['ipAddress']; }
		if (isset($optionalParams['pageAddress'])) { $params['u'] = $optionalParams['pageAddress']; }
	
		return $this->sendMessage("pgr", $params, $validationErrorMsg);
	}

	/*
	* Sends an User Information message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param array $optionalParams An associative array containing paramName => value
	* @param int $optionalParams['birthYear'] The birth year of the user
	* @param string $optionalParams['gender'] The gender of the user (m,f,u)
	* @param string $optionalParams['country'] The 2-character country code of the user
	* @param int $optionalParams['friendCount'] The friend count of the user
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackUserInformation($userId, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array('s' => $userId);
		
		if (isset($optionalParams['birthYear'])) { $params['b'] = $optionalParams['birthYear']; }
		if (isset($optionalParams['gender'])) { $params['g'] = $optionalParams['gender']; }
		if (isset($optionalParams['country'])) { $params['lc'] = strtoupper($optionalParams['country']); }
		if (isset($optionalParams['friendCount'])) { $params['f'] = $optionalParams['friendCount']; }

		return $this->sendMessage("cpu", $params, $validationErrorMsg);
	}

	/*
	* Sends an Goal Count message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param array $optionalParams An associative array containing paramName => value
	* @param int $optionalParams['goalCount1'] The amount to increment goal count 1 by
	* @param int $optionalParams['goalCount2'] The amount to increment goal count 2 by
	* @param int $optionalParams['goalCount3'] The amount to increment goal count 3 by
	* @param int $optionalParams['goalCount4'] The amount to increment goal count 4 by
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackGoalCount($userId, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array('s' => $userId);
		
		if (isset($optionalParams['goalCount1'])) { $params['gc1'] = $optionalParams['goalCount1']; }
		if (isset($optionalParams['goalCount2'])) { $params['gc2'] = $optionalParams['goalCount2']; }
		if (isset($optionalParams['goalCount3'])) { $params['gc3'] = $optionalParams['goalCount3']; }
		if (isset($optionalParams['goalCount4'])) { $params['gc4'] = $optionalParams['goalCount4']; }
	
		return $this->sendMessage("gci", $params, $validationErrorMsg);
	}

	/*
	* Sends an Revenue message to Kontagent.
	*
	* @param string $userId The UID of the user
	* @param int $value The amount of revenue in cents
	* @param array $optionalParams An associative array containing paramName => value
	* @param string $optionalParams['type'] The transaction type (direct, indirect, advertisement, credits, other)
	* @param string $optionalParams['subtype1'] Subtype1 value (max 32 chars)
	* @param string $optionalParams['subtype2'] Subtype2 value (max 32 chars)
	* @param string $optionalParams['subtype3'] Subtype3 value (max 32 chars)
	* @param string $validationErrorMsg The error message on validation failure
	* 
	* @return bool Returns false on validation failure, true otherwise
	*/
	public function trackRevenue($userId, $value, $optionalParams = array(), &$validationErrorMsg = null) {
		$params = array(
			's' => $userId,
			'v' => $value
		);
		
		if (isset($optionalParams['type'])) { $params['tu'] = $optionalParams['type']; }
		if (isset($optionalParams['subtype1'])) { $params['st1'] = $optionalParams['subtype1']; }
		if (isset($optionalParams['subtype2'])) { $params['st2'] = $optionalParams['subtype2']; }
		if (isset($optionalParams['subtype3'])) { $params['st3'] = $optionalParams['subtype3']; }
	
		return $this->sendMessage("mtu", $params, $validationErrorMsg);
	}
}

////////////////////////////////////////////////////////////////////////////////

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
	* @param string $validationErrorMsg If the parameter value is invalid, this will be populated with the error message
	*
	* @returns bool Returns true on success and false on failure.

	*/
	public static function validateParameter($messageType, $paramName, $paramValue, &$validationErrorMsg = null) {
		// generate name of the dynamic method
		$methodName = 'validate' . ucfirst($paramName);
		
		if (!self::$methodName($messageType, $paramValue, $validationErrorMsg)) {
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateB($messageType, $paramValue, &$validationErrorMsg = null) {
		// birthyear param (cpu message)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1900, 'max_range' => 2011)))) {
			$validationErrorMsg = 'Invalid birth year.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateF($messageType, $paramValue, &$validationErrorMsg = null) {
		// friend count param (cpu message)
		if(!filter_var($paramValue, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
			$validationErrorMsg = 'Invalid friend count.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateG($messageType, $paramValue, &$validationErrorMsg = null) {
		// gender param (cpu message)
		if (preg_match('/^[mfu]$/', $paramValue) == 0) {
			$validationErrorMsg = 'Invalid gender.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateGc1($messageType, $paramValue, &$validationErrorMsg = null) {
		// goal count param (gc1, gc2, gc3, gc4 messages)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT, array('options' => array('min_range' => -16384, 'max_range' => 16384)))) {
			$validationErrorMsg = 'Invalid goal count value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateGc2($messageType, $paramValue, &$validationErrorMsg = null) {
		return self::validateGc1($messageType, $paramValue, $validationErrorMsg);
	}
	
	private static function validateGc3($messageType, $paramValue, &$validationErrorMsg = null) {
		return self::validateGc1($messageType, $paramValue, $validationErrorMsg);
	}
	
	private static function validateGc4($messageType, $paramValue, &$validationErrorMsg = null) {
		return self::validateGc1($messageType, $paramValue, $validationErrorMsg);
	}
	
	private static function validateI($messageType, $paramValue, &$validationErrorMsg = null) {
		// isAppInstalled param (inr, psr, ner, nei messages)
		if (preg_match('/^[01]$/', $paramValue) == 0) {
			$validationErrorMsg = 'Invalid isAppInstalled value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateIp($messageType, $paramValue, &$validationErrorMsg = null) {
		// ip param (pgr messages)
		if (!filter_var($paramValue, FILTER_VALIDATE_IP)) {
			$validationErrorMsg = 'Invalid ip address value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateL($messageType, $paramValue, &$validationErrorMsg = null) {
		// level param (evt messages)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
			$validationErrorMsg = 'Invalid level value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateLc($messageType, $paramValue, &$validationErrorMsg = null) {
		// country param (cpu messages)
		if (preg_match('/^[A-Z]{2}$/', $paramValue) == 0) {
			$validationErrorMsg = 'Invalid country value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateLp($messageType, $paramValue, &$validationErrorMsg = null) {
		// postal/zip code param (cpu messages)
		// this parameter isn't being used so we just return true for now
		return true;
	}
	
	private static function validateLs($messageType, $paramValue, &$validationErrorMsg = null) {
		// state param (cpu messages)
		// this parameter isn't being used so we just return true for now
		return true;
	}
	
	private static function validateN($messageType, $paramValue, &$validationErrorMsg = null) {
		// event name param (evt messages)
		if (preg_match('/^[A-Za-z0-9-_]{1,32}$/', $paramValue) == 0) {
			$validationErrorMsg = 'Invalid event name value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateR($messageType, $paramValue, &$validationErrorMsg = null) {
		// Sending messages include multiple recipients (comma separated) and
		// response messages can only contain 1 recipient UID.
		if ($messageType == 'ins' || $messageType == 'nes' || $messageType == 'nts') {
			// recipients param (ins, nes, nts messages)
			if (preg_match('/^[0-9]+(,[0-9]+)*$/', $paramValue) == 0) {
				$validationErrorMsg = 'Invalid recipient user ids.';
				return false;
			}
		} elseif ($messageType == 'inr' || $messageType == 'psr' || $messageType == 'nei' || $messageType == 'ntr') {
			// recipient param (inr, psr, nei, ntr messages)
			if (!filter_var($paramValue, FILTER_VALIDATE_INT)) {
				$validationErrorMsg = 'Invalid recipient user id.';
				return false;
			}
		}
	
		return true;
	}
	
	private static function validateS($messageType, $paramValue, &$validationErrorMsg = null) {
		// userId param
		if (!filter_var($paramValue, FILTER_VALIDATE_INT)) {
			$validationErrorMsg = 'Invalid user id.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateSt1($messageType, $paramValue, &$validationErrorMsg = null) {
		// subtype1 param
		if (preg_match('/^[A-Za-z0-9-_]{1,32}$/', $paramValue) == 0) {
			$validationErrorMsg = 'Invalid subtype value.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateSt2($messageType, $paramValue, &$validationErrorMsg = null) {
		return self::validateSt1($messageType, $paramValue, $validationErrorMsg);
	}
	
	private static function validateSt3($messageType, $paramValue, &$validationErrorMsg = null) {
		return self::validateSt1($messageType, $paramValue, $validationErrorMsg);
	}

	private static function validateSu($messageType, $paramValue, &$validationErrorMsg = null) {
		// short tracking tag param
		if (preg_match('/^[A-Fa-f0-9]{8}$/', $paramValue) == 0) {
			$validationErrorMsg = 'Invalid short unique tracking tag.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateTs($messageType, $paramValue, &$validationErrorMsg = null) {
		// timestamp param (pgr message)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT)) {
			$validationErrorMsg = 'Invalid timestamp.';
			return false;
		} else {
			return true;
		}
	}
	
	private static function validateTu($messageType, $paramValue, &$validationErrorMsg = null) {
		// type parameter (mtu, pst/psr, ucc messages)
		// acceptable values for this parameter depends on the message type
		if ($messageType == 'mtu') {
			if (preg_match('/^(direct|indirect|advertisement|credits|other)$/', $paramValue) == 0) {
				$validationErrorMsg = 'Invalid monetization type.';
				return false;
			}
		} elseif ($messageType == 'pst' || $messageType == 'psr') {
			if (preg_match('/^(feedpub|stream|feedstory|multifeedstory|dashboard_activity|dashboard_globalnews)$/', $paramValue) == 0) {
				$validationErrorMsg = 'Invalid stream post/response type.';
				return false;
			}
		} elseif ($messageType == 'ucc') {
			if (preg_match('/^(ad|partner)$/', $paramValue) == 0) {
				$validationErrorMsg = 'Invalid third party communication click type.';
				return false;
			}
		}
		
		return true;
	}
	
	private static function validateU($messageType, $paramValue, &$validationErrorMsg = null) {
		// unique tracking tag parameter for all messages EXCEPT pgr.
		// for pgr messages, this is the "page address" param
		if ($messageType != 'pgr') {
			if (preg_match('/^[A-Fa-f0-9]{32}$/', $paramValue) == 0) {
				$validationErrorMsg = 'Invalid unique tracking tag.';
				return false;
			}
		}
		
		return true;
	}
	
	private static function validateV($messageType, $paramValue, &$validationErrorMsg = null) {
		// value param (mtu, evt messages)
		if (!filter_var($paramValue, FILTER_VALIDATE_INT)) {
			$validationErrorMsg = 'Invalid value.';
			return false;
		} else {
			return true;
		}
	}
}
