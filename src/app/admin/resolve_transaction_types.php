<?php

function validate($data)
{
  $expected_keys = ['admin_type_id'];

  return expect_keys($data, $expected_keys);
}

if (!checkGetMethod()) {
  return;
}

$user = auth();

if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

if (!checkUserType('admin')) {
  response(['message' => "Unauthorized"], 401);
  return;
}

if (!validate($_GET)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $admin_type_id = $_GET['admin_type_id'];

  $sql = "SELECT * from transaction_desc INNER JOIN counter_tbl on transaction_desc.counter_id = counter_tbl.id WHERE admin_type_id = :admin_type_id";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":admin_type_id", $admin_type_id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    response(['data' => $data]);
  } else {
    response(['message' => 'Not Found'], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
