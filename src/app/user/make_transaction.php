<?php

function make_transaction($conn, $student_id, $amount, $transaction_desc_id, $admin_id)
{
  $sql = "INSERT INTO transaction (student_id, amount, transaction_desc_id, admin_id) VALUES((select id from student where student_id = :student_id LIMIT 1), :amount, :transaction_desc_id, :admin_id)";

  $stmt = $conn->prepare($sql);
  $stmt->bindValue(":student_id", $student_id);
  $stmt->bindValue(":amount", $amount);
  $stmt->bindValue(":transaction_desc_id", $transaction_desc_id);
  $stmt->bindValue(":admin_id", $admin_id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    return true;
  } else {
    return false;
  }
}
