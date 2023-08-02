<?php

function auth()
{
  if (isset($_SESSION['auth'])) {
    return $_SESSION['auth'];
  } else {
    return false;
  }
}

function auth_type()
{
  if (isset($_SESSION['auth_type'])) {
    return $_SESSION['auth_type'];
  } else {
    return false;
  }
}

function checkUserType($type)
{
  if (auth() && auth_type() != $type) {
    response("", 401);
    return false;
  }
  return true;
}

function checkPostMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "POST") {
    response(['message' => "Method Not allowed"], 405);
    return false;
  }
  return true;
}

function checkGetMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "GET") {
    response(['message' => "Method Not allowed"], 405);
    return false;
  }
  return true;
}

function checkPutMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "PUT") {
    response(['message' => "Method Not allowed"], 405);
    return false;
  }
  return true;
}

function checkDeleteMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "DELETE") {
    response(['message' => "Method Not allowed"], 405);
    return false;
  }
  return true;
}

function response($response, $code = 200, $headers = null)
{
  $headers = [
    'Access-Control-Allow-Origin' => 'http://rbuaps.com:8000',
    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Allow-Headers' => 'Content-Type',
    'Access-Control-Allow-Credentials' => 'true'
  ];

  if ($headers != null) {
    foreach ($headers as $name => $data) {
      header("$name: $data");
    }
  }

  http_response_code($code);
  if ($response != "") {
    echo json_encode($response);
  }
}

function expect_keys($data, $expected_keys)
{
  if ($data) {
    foreach ($data as $key => $value) {
      if (!in_array($key, $expected_keys) && empty($value)) {
        return false;
      }
    }
  } else {
    return false;
  }

  return true;
}
