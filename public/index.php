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
    'Access-Control-Allow-Origin' => 'http://libraryman.com',
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
