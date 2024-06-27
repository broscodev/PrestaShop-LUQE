<?php

require(dirname(__FILE__) . '/../config/config.inc.php');
require_once(dirname(__FILE__) . '/../init.php');

function createNewUser($email, $password, $firstName, $lastName)
{
    $customer = new Customer();

    $customer->email = $email;
    $customer->passwd = Tools::encrypt($password);
    $customer->firstname = $firstName;
    $customer->lastname = $lastName;
    $customer->id_gender = 1; // Gender: 1 for Male, 2 for Female
    $customer->birthday = '1990-01-01'; // Birthdate
    $customer->newsletter = 1; // Subscribe to newsletter
    $customer->optin = 1; // Opt-in for third-party offers
    $customer->active = 1; // Active status

    if ($customer->add()) {
        return true;
    } else {
        return false;
    }
}

function existsCustomer($email)
{
    $customer = new Customer();
    $customer = $customer->getByEmail($email);

    if ($customer) {
        return true;
    } else {
        return false;
    }
}

function createSessionForUser($email) {

    Hook::exec('actionAuthenticationBefore');

    // Load PrestaShop context
    $context = Context::getContext();
    // Attempt to authenticate the user
    $customer = new Customer();
    $authentication = $customer->getByEmail($email);

    $context->updateCustomer($customer);

    Hook::exec('actionAuthentication', ['customer' => $context->customer]);

    // Login information have changed, so we check if the cart rules still apply
    CartRule::autoRemoveFromCart($context);
    CartRule::autoAddToCart($context);

    return $context->customer->id;

}

function getMichiSession($accessToken) {

    $apiKey = "23a2935567070a2bd869@53a6b098-b65e-497f-8ec6-1d801217712e@29bf12aeb9ec3386d9ea";
    $url = "https://api-staging.brosco.com.py/brosco-api/brosco-auth/auth/session/$accessToken"; // Replace with the API endpoint
    $headers = array(
        "X-RshkMichi-ApiKey: $apiKey", // Replace with your actual token
        "X-RshkMichi-AccessToken: $accessToken", // Replace with your actual token
        'Content-Type: application/json'
    );

// Initialize cURL session
    $ch = curl_init($url);

// Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_HTTPGET, true); // Use GET method
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Set custom headers

// Execute cURL session
    $response = curl_exec($ch);

    $ret = null;

// Check for cURL errors
    if (!($response === false)) {
        // Process the response
        $ret = json_decode($response, true);
    } else {
        // Handle cURL error
        $ret = array('code' => 'i1000', 'message' => curl_error($ch));
    }

    curl_close($ch);

    return $ret;

}

// CHECK ACCESS TOKEN

// Define the new user details
$at = $_POST['accessToken'];

$session = getMichiSession($at);

if ($session['code'] != null && array_key_exists('message', $session)) {
    header("Location: /brosco/error.php?code=" . $session['code'] . "&message=" . $session['message']);
    return;
} else {
    $userInfo = $session['userInfo'];
    if ($userInfo == null) {
        header("Location: /brosco/error.php?code=I1001&message=NO+USER+INFO");
        return;
    }

    $email = $userInfo['email'];
    $new_user = false;

    if (!existsCustomer($email)) {
        $password = Tools::passwdGen(30);
        $firstName = $userInfo['fullName'];
        $lastName = $userInfo['fullName'];
        $new_user = true;
        createNewUser($email, $password, $firstName, $lastName);
    }

    createSessionForUser($email);

    if ($new_user) {
        header("Location: /brosco/new_user.php?email=" . $email);
    } else {
        // just go to the home page
        header("Location: /");
    }

}

