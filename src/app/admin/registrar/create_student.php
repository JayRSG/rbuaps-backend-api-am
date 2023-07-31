<?php

function validate($data)
{
  $expected_keys = ['first_name', 'last_name', 'email', 'password', 'phone', 'father_name', 'mother_name', 'guardian_phone', 'student_id', 'rfid'];

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
  $father_name = $_POST['father_name'];
  $mother_name = $_POST['mother_name'];
  $guardian_phone = $_POST['guardian_phone'];
  $student_id = $_POST['student_id'];
  $rfid = $_POST['rfid'];

  $params = [];

  $sql = "INSERT INTO $user_type (first_name, last_name, email, password, phone, father_name, mother_name, guardian_phone, student_id, rfid) VALUES(:first_name, :last_name, :email, :password, :phone, :father_name, :mother_name, :guardian_phone, :student_id, :rfid)";

  $stmt = $conn->prepare($sql);

  $params = [
    ":first_name" => $first_name,
    ":last_name" => $last_name,
    ":email" => $email,
    ":password" => $password,
    ":phone" => $phone,
    ":father_name" => $father_name,
    ":mother_name" => $mother_name,
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
