Overview
-----------------

This is a PHP wrapper around Kontagent's API. It provides methods to make the API calls for all the different message types supported by Kontagent.

Getting Started
-----------------

To get started with the Kontagent library, you will need to check-out the kontagent.php file and include it in your project. You will also need to instantiate and configure
an instance of the Kontagent object.

    <?php

    // include the library
    require_once('./kontagent.php');

    // configure and instantiate Kontagent object
    $kt = new Kontagent($ktApiKey, $ktSecretKey, $useTestServer);

    ?>

Using The Library
-----------------

Once you've got your Kontagent object instantiated and configured you can start using the library. Essentially, there are two types of methods provided in the library: tracking methods and helper methods.

**Tracking Methods**

The tracking methods should get called by your application whenever you need to report an event to Kontagent. There is a tracking method available for every message type in the Kontagent API. A few examples are:

    <?php

    $kt->trackApplicationAdded($userId, $uniqueTrackingTag = null, $shortUniqueTrackingTag = null);

    $kt->trackPageRequest($userId, $timestamp, $ipAddress = null, $pageAddress = null);

    $kt->trackEvent($userId, $eventName, $value = null, $level = null, $subtype1 = null, $subtype2 = null, $subtype3 = null);

    $kt->trackRevenue($userId, $value, $type = null,  $subtype1 = null, $subtype2 = null, $subtype3 = null);

    ?>

Everytime events happen within your application, you should make the appropriate call to Kontagent - we will then crunch and analyze this data in our systems and present them to you in your dashboard.

For a full list of the available tracking methods see the "Full Class Reference" section below.

**Helper Methods**

The library provides a few helper methods for common tasks. Currently the only ones available are:

    <?php

    $kt->genUniqueTrackingTag();

    $kt->genShortUniqueTrackingTag();

    ?>

Which will help you generate the tracking tag parameters required to link certain messages together (for example: invite sent -> invite response -> application added).

Examples
-----------------

1. The Basics (page request, user demographic, installs)
2. Invites (same as stream posts)
3. Third-Party Communication Click (ad/partner click tracking)
4. Custom Events


Full Class Reference
-----------------

    /*
    * Generates a unique tracking tag.
    *
    * @return string The unique tracking tag
    */
    public function genUniqueTrackingTag()
    
    
    /*
    * Generates a short unique tracking tag.
    *
    * @return string The short unique tracking tag
    */
    public function genShortUniqueTrackingTag()

    
    /*
    * Sends an Invite Sent message to Kontagent.
    *
    * @param string $userId The UID of the sending user
    * @param string $recipientUserIds A comma-separated list of the recipient UIDs
    * @param string $uniqueTrackingTag 32-digit hex string used to match 
    *    InviteSent->InviteResponse->ApplicationAdded messages. 
    *    See the genUniqueTrackingTag() helper method.
    * @param string $subtype1 Subtype1 value (max 32 chars)
    * @param string $subtype2 Subtype2 value (max 32 chars)
    * @param string $subtype3 Subtype3 value (max 32 chars)
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackInviteSent($userId, $recipientUserIds, $uniqueTrackingTag, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)

    
    /*
    * Sends an Invite Response message to Kontagent.
    *
    * @param string $uniqueTrackingTag 32-digit hex string used to match 
    *    InviteSent->InviteResponse->ApplicationAdded messages. 
    *    See the genUniqueTrackingTag() helper method.
    * @param string $recipientUserId The UID of the responding user
    * @param string $subtype1 Subtype1 value (max 32 chars)
    * @param string $subtype2 Subtype2 value (max 32 chars)
    * @param string $subtype3 Subtype3 value (max 32 chars)
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackInviteResponse($uniqueTrackingTag, $recipientUserId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)

    
    /*
    * Sends an Notification Sent message to Kontagent.
    *
    * @param string $userId The UID of the sending user
    * @param string $recipientUserIds A comma-separated list of the recipient UIDs
    * @param string $uniqueTrackingTag 32-digit hex string used to match 
    *    NotificationSent->NotificationResponse->ApplicationAdded messages. 
    *    See the genUniqueTrackingTag() helper method.
    * @param string $subtype1 Subtype1 value (max 32 chars)
    * @param string $subtype2 Subtype2 value (max 32 chars)
    * @param string $subtype3 Subtype3 value (max 32 chars)
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackNotificationSent($userId, $recipientUserIds, $uniqueTrackingTag, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)


    /*
    * Sends an Notification Response message to Kontagent.
    *
    * @param string $uniqueTrackingTag 32-digit hex string used to match 
    *    NotificationSent->NotificationResponse->ApplicationAdded messages. 
    *    See the genUniqueTrackingTag() helper method.
    * @param string $recipientUserId The UID of the responding user
    * @param string $subtype1 Subtype1 value (max 32 chars)
    * @param string $subtype2 Subtype2 value (max 32 chars)
    * @param string $subtype3 Subtype3 value (max 32 chars)
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackNotificationResponse($uniqueTrackingTag, $recipientUserId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)

    
    /*
    * Sends an Notification Email Sent message to Kontagent.
    *
    * @param string $userId The UID of the sending user
    * @param string $recipientUserIds A comma-separated list of the recipient UIDs
    * @param string $uniqueTrackingTag 32-digit hex string used to match 
    *    NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
    *    See the genUniqueTrackingTag() helper method.
    * @param string $subtype1 Subtype1 value (max 32 chars)
    * @param string $subtype2 Subtype2 value (max 32 chars)
    * @param string $subtype3 Subtype3 value (max 32 chars)
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackNotificationEmailSent($userId, $recipientUserIds, $uniqueTrackingTag, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)


    /*
    * Sends an Notification Email Response message to Kontagent.
    *
    * @param string $uniqueTrackingTag 32-digit hex string used to match 
    *    NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
    *    See the genUniqueTrackingTag() helper method.
    * @param string $recipientUserId The UID of the responding user
    * @param string $subtype1 Subtype1 value (max 32 chars)
    * @param string $subtype2 Subtype2 value (max 32 chars)
    * @param string $subtype3 Subtype3 value (max 32 chars)
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackNotificationEmailResponse($uniqueTrackingTag, $recipientUserId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)


    /*
    * Sends an Stream Post message to Kontagent.
    *
    * @param string $userId The UID of the sending user
    * @param string $uniqueTrackingTag 32-digit hex string used to match 
    *    NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
    *    See the genUniqueTrackingTag() helper method.
    * @param string $type The Facebook channel type
    *    (feedpub, stream, feedstory, multifeedstory, dashboard_activity, or dashboard_globalnews).
    * @param string $subtype1 Subtype1 value (max 32 chars)
    * @param string $subtype2 Subtype2 value (max 32 chars)
    * @param string $subtype3 Subtype3 value (max 32 chars)
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackStreamPost($userId, $uniqueTrackingTag, $type, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)


    /*
    * Sends an Stream Post Response message to Kontagent.
    *
    * @param string $uniqueTrackingTag 32-digit hex string used to match 
    *    NotificationEmailSent->NotificationEmailResponse->ApplicationAdded messages. 
    *    See the genUniqueTrackingTag() helper method.
    * @param string $type The Facebook channel type
    *    (feedpub, stream, feedstory, multifeedstory, dashboard_activity, or dashboard_globalnews).
    * @param string $recipientUserId The UID of the responding user
    * @param string $subtype1 Subtype1 value (max 32 chars)
    * @param string $subtype2 Subtype2 value (max 32 chars)
    * @param string $subtype3 Subtype3 value (max 32 chars)
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackStreamPostResponse($uniqueTrackingTag, $type, $recipientUserId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)


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


    /*
    * Sends an Application Added message to Kontagent.
    *
    * @param string $userId The UID of the installing user
    * @param string $uniqueTrackingTag 16-digit hex string used to match 
    *    Invite/StreamPost/NotificationSent/NotificationEmailSent->ApplicationAdded messages. 
    *    See the genUniqueTrackingTag() helper method.
    * @param string $shortUniqueTrackingTag 8-digit hex string used to match 
    *    ThirdPartyCommClicks->ApplicationAdded messages. 
    *    See the genShortUniqueTrackingTag() helper method.
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackApplicationAdded($userId, $uniqueTrackingTag = null, $shortUniqueTrackingTag = null, &$errorMessage = null)


    /*
    * Sends an Application Removed message to Kontagent.
    *
    * @param string $userId The UID of the removing user
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackApplicationRemoved($userId, &$errorMessage = null)

    
    /*
    * Sends an Third Party Communication Click message to Kontagent.
    *
    * @param string $type The third party comm click type (ad, partner).
    * @param string $shortUniqueTrackingTag 8-digit hex string used to match 
    *    ThirdPartyCommClicks->ApplicationAdded messages. 
    * @param string $userId The UID of the user
    * @param string $subtype1 Subtype1 value (max 32 chars)
    * @param string $subtype2 Subtype2 value (max 32 chars)
    * @param string $subtype3 Subtype3 value (max 32 chars)
    * @param string $errorMessage The error message on failure
    * 
    * @return bool Returns true on success, false otherwise
    */
    public function trackThirdPartyCommClick($type, $shortUniqueTrackingTag, $userId = null, $subtype1 = null, $subtype2 = null, $subtype3 = null, &$errorMessage = null)


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
