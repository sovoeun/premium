<?php
session_start();
require_once 'autoload.php';
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;

FacebookSession::setDefaultApplication('673630099468768', '33ea910020d0c64da7d9279c7b9cd431');
$helper = new FacebookRedirectLoginHelper('http://localhost/pre-test/app/Libraries/fbconfig.php');

//FacebookSession::setDefaultApplication('340660092989989', '0a08dff9ed30d7353d38165b6eedb189');
//$helper = new FacebookRedirectLoginHelper('http://angkorebuy.com/test/app/Libraries/fbconfig.php');

try {
    $session = $helper->getSessionFromRedirect();
} catch (FacebookRequestException $ex) {
    // When Facebook returns an error
} catch (Exception $ex) {
    // When validation fails or other local issues
}

if (isset($session)) {

    $fields = array('id', 'email','name', 'first_name', 'last_name', 'link', 'website','gender', 'locale', 'about', 'hometown', 'location');
    $request = new FacebookRequest($session, 'GET', '/me?fields=' . implode(',', $fields));
    $response = $request->execute();
    $graphObject = $response->getGraphObject();
    $fbid = $graphObject->getProperty('id');
    $fbfullname = $graphObject->getProperty('name');
    $femail = $graphObject->getProperty('email');
    $username = $graphObject->getProperty('username');
    $_SESSION['FBID'] = $fbid;
    $_SESSION['FULLNAME'] = $fbfullname;
    $_SESSION['EMAIL'] = $femail;
    $_SESSION['USERNAME'] = $username;
    header("Location: ../../index.php");
} else {

    $loginUrl = $helper->getLoginUrl();
    header("Location: " . $loginUrl);
}


?>
