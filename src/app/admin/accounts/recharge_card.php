<?php

require_once __DIR__ . "../../../user/add_recharge_history.php";

function validate($data)
{
  $expected_keys = ['student_id', 'rfid', 'recharge_amount'];
  return expect_keys($data, $expected_keys);
}

if (!checkPostMethod()) {
  return;
}

$user = auth();

if (!$user) {
  response(['message' => 'Unauthenticated'], 401);
  return;
}



if (auth_type() != "admin") {
  response(['message' => 'Unauthorized'], 401);
  return;
}

if (isset($user['admin_type']) && $user['admin_type'] != 2) {
  response(['message' => "Unauthorized"], 401);
  return;
}


if (!validate($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}


try {
  $student_id = $_POST['student_id'];   //id
  $rfid = $_POST['rfid'];
  $recharge_amount = $_POST['recharge_amount'];

  $sql = "SELECT * from student WHERE student_id = :student_id AND rfid = :rfid";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":student_id", $student_id);
  $stmt->bindParam(":rfid", $rfid);

  $result = $stmt->execute();

  if ($stmt->rowCount() <= 0) {
    response(['message' => "Invalid data"], 400);
    return;
  }

  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  $student_id = $data['id'];


  $sql = "INSERT INTO card_status (student_id, balance)  VALUES (:student_id, :recharge_amount) ON DUPLICATE KEY UPDATE balance = balance + :recharge_amount";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":student_id", $student_id);
  $stmt->bindParam(":recharge_amount", $recharge_amount);

  $result = $stmt->execute();


  if ($result && $stmt->rowCount() > 0) {
    if (add_recharge_history($conn, $student_id, $user['id'], $recharge_amount)) {
      response(['message' => "Recharge Successful"], 200);
    } else {
      $sql = "UPDATE card_status SET balance = balance - :recharge_amount where student_id = :student_id";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":recharge_amount", $recharge_amount);
      $stmt->bindParam(":student_id", $student_id);

      $result = $stmt->execute();
      response(['message' => "Recharge Failed 1"], 400);
    }
  } else {
    response(['message' => "Recharge Failed 2"], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
