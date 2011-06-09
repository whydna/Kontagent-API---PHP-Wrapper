Overview

Getting Started
-----------------

To get started with the Kontagent library, you will need to check-out the kontagent.php file and include it in your project. You will also need to instantiate and configure
an instance of the Kontagent object.

<?php

// include the kontagent library
require_once('./kontagent.php');

// instantiate/configure Kontagent object
$ktApiKey = 'your_kt_api_key';
$ktSecretKey = 'your_kt_secret_key';
$useTestServer = true;

$kt = new Kontagent($ktApiKey, $ktSecretKey, $useTestServer);

?>

Using The Library
-----------------

Once you've got your Kontagent object instantiated and configured you can start using the library. Essentially, there are two types of methods provided in the library: tracking methods and helper methods.

** Tracking Methods **

The tracking methods should get called by your application whenever you need to report an event to Kontagent. There is a tracking method available for every message type in the Kontagent API. A few examples are:

<?php

$kt->trackApplicationAdded($userId, $uniqueTrackingTag = null, $shortUniqueTrackingTag = null)
$kt->trackPageRequest($userId, $timestamp, $ipAddress = null, $pageAddress = null)
$kt->trackEvent($userId, $eventName, $value = null, $level = null, $subtype1 = null, $subtype2 = null, $subtype3 = null)
$kt->trackRevenue($userId, $value, $type = null,  $subtype1 = null, $subtype2 = null, $subtype3 = null)

?>

Everytime events happen within your application, you should make the appropriate call to Kontagent - we will then crunch and analyze this data in our systems and present them to you in your dashboard.

For a full list of the available tracking methods see: <link_to_full_reference>

** Helper Methods **

The library provides a few helper methods for common tasks. Currently the only ones available are:

<?php

$kt->genUniqueTrackingTag();
$kt->genShortUniqueTrackingTag();

?>

Examples
-----------------

1. The Basics
2. Installs and User Demographic
3. Invites
4. Events
5. Revenue

Full Class Reference
-----------------
