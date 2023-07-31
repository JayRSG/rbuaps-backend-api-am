<?php

if (!checkGetMethod()) {
  return;
}

$user = auth();

if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

if (!checkUserType("student")) {
  return;
}

try {
  $student_id = $user['id'];

  $sql = "SELECT * from recharge_history WHERE student_id = :student_id GROUP BY created_at DESC";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":student_id", $student_id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $recharge_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($recharge_data) {
      response(['data' => $recharge_data]);
    } else {
      response(['message' => "Not Found"], 404);
    }
  } else {
    response(['message' => "Not Found"], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
