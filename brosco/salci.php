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
        echo 'Customer created successfully!';
    } else {
        echo 'Failed to create customer.';
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

// Define the new user details
$email = $_POST['email'];
$password = ToolsCore::passwdGen(30);
$firstName = $_POST['name'];
$lastName = $_POST['lastname'];

if (existsCustomer($email)) {
    echo 'User already exists!';
    return;
} else {
    createNewUser($email, $password, $firstName, $lastName);
    echo 'User created';
}

?>
