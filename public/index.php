<?php

session_start();
define('root', '../../src');
define('app',  root . '/app');

require_once __DIR__ . "../../vendor/autoload.php";
require_once __DIR__ . "../../src/lib/utils.php";


// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  // Enable CORS
  response("", 204, [
    'Access-Control-Allow-Origin' => 'http://rbuaps.com:3000',
    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE',
    'Access-Control-Allow-Headers' => 'Content-Type',
    'Access-Control-Allow-Credentials' => 'true'
  ]);
  return;
}


// Load the .env file for accessing client secrets
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

// Get the config information
require_once __DIR__ . "../../src/config/config.php";

// Get the request method GET, POST, PUT, DELETE
$request = $_SERVER['REQUEST_URI'];

// Extract the path from the request URI -> /, /books, /user etc.
$path = parse_url($request, PHP_URL_PATH);

// Extract the query string from the request URI  -> /user?param=value => param is the query
$queryString = parse_url($request, PHP_URL_QUERY);

parse_str($queryString, $queryParams);

// Define the http routes 
$routes = [
  "/"                                 =>       app . "/home.php",
  "/login"                            =>       app . "/login.php",
  "/logout"                           =>       app . "/logout.php",
  "/my_profile"                       =>       app . "/my_profile.php",
  "/admin/transaction_types"          =>       app . "/admin/resolve_transaction_types.php",
  "/admin/get_users"                  =>       app . "/admin/registrar/get_users.php",
  "/admin/admin_types"                =>       app . "/admin/registrar/admin_types.php",
  "/admin/create_admin"               =>       app . "/admin/registrar/create_admin.php",
  "/admin/create_student"             =>       app . "/admin/registrar/create_student.php",
  "/admin/card_reissue"               =>       app . "/admin/registrar/issue_rfid_card.php",
  "/admin/deactivate_user"            =>       app . "/admin/registrar/deactivate_user.php",
  "/accounts/recharge_card"           =>       app . "/admin/accounts/recharge_card.php",
  "/accounts/tuition_payments"        =>       app . "/admin/accounts/tuition_payments.php",
  "/accounts/transaction_history"     =>       app . "/admin/accounts/transaction_history.php",
  "/canteen/add_item"                 =>       app . "/admin/canteen/add_item.php",
  "/canteen/sell"                     =>       app . "/admin/canteen/sell.php",
  "/canteen/get_items"                =>       app . "/admin/canteen/get_items.php",
  "/user/card_status"                 =>       app . "/user/card_status.php",
  "/user/recharge_history"            =>       app . "/user/recharge_history.php",
  "/user/transaction_history"         =>       app . "/user/transaction_history.php",

];

// Check if the requested route exists in the routes array
if (array_key_exists($path, $routes)) {
  $file = __DIR__ . $routes[$path];

  // Pass the query parameters to the file using $_GET
  $_GET = $queryParams;

  // include file according to the route
  require_once $file;
} else {
  // if the route not found return a 404 message
  response(['message' => "Route Not Found"], 404);
}
