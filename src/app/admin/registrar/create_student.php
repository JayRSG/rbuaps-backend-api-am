<?php

function validate($data)
{
  $expected_keys = ['first_name', 'last_name', 'email', 'password', 'phone', 'fathers_name', 'mothers_name', 'guardian_phone', 'student_id', 'rfid'];

  return expect_keys($data, $expected_keys);
}

if (!checkPostMethod()) {
  return;
}

$user = auth();
if (!checkUserType("admin") || $user['admin_type'] != 1) {
  response(['message' => "Unauthorized Action"]);
  return;
}

if (!validate($_POST)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $email = $_POST['email'];
  $password = password_hash('12345678', PASSWORD_DEFAULT);
  $phone = $_POST['phone'];
  $fathers_name = $_POST['fathers_name'];
  $mothers_name = $_POST['mothers_name'];
  $guardian_phone = $_POST['guardian_phone'];
  $student_id = $_POST['student_id'];
  $rfid = $_POST['rfid'];

  $params = [];

  $sql = "INSERT INTO student (first_name, last_name, email, password, phone, fathers_name, mothers_name, guardian_phone, student_id, rfid) VALUES(:first_name, :last_name, :email, :password, :phone, :fathers_name, :mothers_name, :guardian_phone, :student_id, :rfid)";


  $stmt = $conn->prepare($sql);

  $params = [
    ":first_name" => $first_name,
    ":last_name" => $last_name,
    ":email" => $email,
    ":password" => $password,
    ":phone" => $phone,
    ":fathers_name" => $fathers_name,
    ":mothers_name" => $mothers_name,
    ":guardian_phone" => $guardian_phone,
    ":student_id" => $student_id,
    ":rfid" => $rfid,
  ];

  foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
  }

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => "User Created"]);
  } else {
    response(['message' => "User creation failed"]);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
