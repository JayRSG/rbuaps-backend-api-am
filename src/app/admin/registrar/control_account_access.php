<?php

function validate($data)
{
  $expected_keys = ['id', 'account_type', 'active'];

  if (expect_keys($data, $expected_keys)) {
    if ($data['account_type'] == "admin" || $data['account_type'] == "student") {
      return true;
    } else {
      return false;
    }
  }
}

if (!checkPostMethod()) {
  return;
}

$user = auth();
if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

if (!checkUserType("admin") || $user['admin_type'] != 1) {
  response(['message' => "Unauthorized Action"]);
  return;
}

if (!validate($_POST)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $id = $_POST['id'] ?? null;
  $account_type = $_POST['account_type'];
  $status = $_POST['active'];

  $sql = "UPDATE $account_type set active = :active where id = :id";
  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":id", $id);
  $stmt->bindParam(":active", $status);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => 'Account Status Updated'], 200);
  } else {
    response(['message' => 'Status Update Failed'], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
