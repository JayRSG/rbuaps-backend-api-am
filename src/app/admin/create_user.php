<?php

function validate($data)
{
  $expected_keys = ['first_name', 'last_name', 'email', 'phone', 'user_type'];
  $dependent_key = [];
  $found = false;
  foreach ($data as $key => $value) {
    $found = array_search($key, $expected_keys);

    if (!$found) {
      break;
    }
  }
  if ($found) {
    if ($data['user_type'] == "admin") {
      $dependent_key = ['admin_type'];
    } else {
      $dependent_key = ['rfid', 'student_id'];
    }

    foreach ($data as $key => $value) {
      $found = array_search($key, $dependent_key);

      if (!$found) {
        break;
      }
    }
  }

  return $found;
}

if (!checkPostMethod()) {
  return;
}

$user = auth();
if (!checkUserType("admin") && $user['id'] == 1) {
  return;
}

try {
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $email = $_POST['email'];
  $password = password_hash('12345', PASSWORD_DEFAULT);
  $phone = $_POST['phone'];
  $user_type = $_POST['user_type'];   // admin || student

  if ($user_type == "admin") {
    $admin_type = $_POST['admin_type'];
  } else if ($user_type == "student") {
    $rfid = $_POST['rfid'];
    $student_id = $_POST['student_id'];
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
