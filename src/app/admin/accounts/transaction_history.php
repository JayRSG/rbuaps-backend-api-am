<?php

if (!checkGetMethod()) {
  return;
}

$user = auth();
if (!$user) {
  response(['messsage' => "Unauthenticated"]);
  return;
}

if (!checkUserType('admin')) {
  return;
}

if (!$user['admin_type'] == 2) {
  response(['messsage' => "Unauthorized Access"]);
  return;
}

try {
  $student_id = $_GET['student_id'] ?? null;
  $admin_type_id = $_GET['admin_type_id'] ?? null;
  $transaction_desc_id = isset($_GET['transaction_desc_id']) ? $_GET['transaction_desc_id'] : null;

  $from_date = !empty($_POST['from_date']) ? $_POST['from_date'] : null;
  $to_date = !empty($_POST['to_date']) ? $_POST['to_date'] : null;
  $param = array();

  $sql = "SELECT t.*, transaction_desc.transaction_description, counter_tbl.counter_name from transaction as t
  INNER JOIN transaction_desc on transaction_desc_id = transaction_desc.id
  INNER JOIN counter_tbl on transaction_desc.counter_id = counter_tbl.id

  WHERE student_id = (select id from student where student_id = :student_id) AND counter_tbl.admin_type_id = :admin_type_id AND";

  $param[':student_id'] = $student_id;
  $param[':admin_type_id'] = $admin_type_id;

  if ($transaction_desc_id != null) {
    $sql .= " transaction_desc_id = :transaction_desc_id AND";
    $param[':transaction_desc_id'] = $transaction_desc_id;
  }

  if ($from_date != null && $to_date != null) {
    $sql .= " payment_date BETWEEN :from_date AND :to_date AND";
    $param[':from_date'] = $from_date;
    $param[':to_date'] = $to_date;
  }


  $sql = rtrim($sql, 'AND ');
  $stmt = $conn->prepare($sql);

  foreach ($param as $key => $value) {
    $stmt->bindValue($key, $value);
  }


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
