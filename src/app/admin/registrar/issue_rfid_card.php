<?php

function validate($data)
{
  $expected_keys = ['rfid', 'user_id'];

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
  $user_id = $_POST['user_id'];
  $rfid = $_POST['rfid'];
  $student_id = $_POST['student_id'];

  $sql = "UPDATE student set rfid = :rfid where id = :user_id AND student_id = :student_id";
  $stmt = $conn->prepare($sql);

  $stmt->bindValue(":rfid", $rfid);
  $stmt->bindValue(":student_id", $student_id);
  $stmt->bindValue(":user_id", $user_id);

  $result = $stmt->execute();


  if ($result && $stmt->rowCount() > 0) {
    response(['message' => 'RFID issued']);
  } else {
    response(['message' => 'RFID Couldn\'t be issued'], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
