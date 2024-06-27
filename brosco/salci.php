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

// Define the new user details
$email = $_POST['email'];
$password = ToolsCore::passwdGen(30);
$firstName = $_POST['name'];
$lastName = $_POST['lastname'];

$created = false;

if (existsCustomer($email)) {
    //
} else {
    $created = true;
    createNewUser($email, $password, $firstName, $lastName);
}

$user_id = createSessionForUser($email);

echo "User created: $created<br> -> User ID is: $user_id";
