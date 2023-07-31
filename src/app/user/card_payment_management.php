<?php

function deduct_account_balance($conn, $student_id, $rfid, $amount)
{
  $sql = "UPDATE card_status set balance = balance - :amount where student_id = (select id from student WHERE student_id = :student_id AND rfid = :rfid LIMIT 1)";
  $stmt = $conn->prepare($sql);

  $stmt->bindValue(":student_id", $student_id);
  $stmt->bindValue(":amount", $amount);
  $stmt->bindValue(":rfid", $rfid);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    return true;
  } else {
    response(['message' => 'Failed processing card'], 400);
    return false;
  }
}

function refund_account_balance($conn, $student_id, $rfid, $amount)
{
  $sql = "UPDATE card_status set balance = balance + :amount where student_id = (select id from student WHERE student_id = :student_id AND rfid = :rfid LIMIT 1)";
  $stmt = $conn->prepare($sql);

  $stmt->bindValue(":student_id", $student_id);
  $stmt->bindValue(":amount", $amount);
  $stmt->bindValue(":rfid", $rfid);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    return true;
  } else {
    response(['message' => 'Failed processing card'], 400);
    return false;
  }
}

function verify_account_info($conn, $student_id, $rfid, $amount)
{
  // Check student identity 

  $sql = "SELECT * from student where student_id = :student_id AND rfid = :rfid AND active = 1 LIMIT 1";

  $stmt = $conn->prepare($sql);
  $stmt->bindValue(":student_id", $student_id);
  $stmt->bindValue(":rfid", $rfid);
  $result = $stmt->execute();


  if ($stmt->rowCount() <= 0) {
    response(['message' => "Account Not Found"], 404);
    return false;
  }

  $id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

  // check card status and get balance, compare balance info with required amount

  $sql = "SELECT * from card_status WHERE student_id = :id";
  $stmt = $conn->prepare($sql);

  $stmt->bindValue(":id", $id);
  $result = $stmt->execute();

  if ($stmt->rowCount() <= 0) {
    response(['message' => "Account Information unavailable"], 404);
    return false;
  }

  $card_status = $stmt->fetch(PDO::FETCH_ASSOC);
  $card_balance = $card_status['balance'];

  if ($card_balance < $amount) {
    response(['message' => 'Insufficient Funds'], 403);
    return false;
  }

  return $card_balance;
}
