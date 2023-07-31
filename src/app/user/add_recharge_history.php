<?php

function add_recharge_history($conn, $student_id, $admin_id, $recharge_amount)
{
  $sql = "INSERT INTO recharge_history (student_id, recharge_amount, admin_id) VALUES (:student_id, :recharge_amount, :admin_id)";

  $stmt = $conn->prepare($sql);
  $stmt->bindValue(":student_id", $student_id);
  $stmt->bindValue(":admin_id", $admin_id);
  $stmt->bindValue(":recharge_amount", $recharge_amount);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    return true;
  } else {
    return false;
  }
}
