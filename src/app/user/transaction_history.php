<?php

if (!checkPostMethod()) {
  return;
}

$user = auth();
if (!$user) {
  response(['messsage' => "Unauthenticated"]);
  return;
}

if (!checkUserType('student')) {
  return;
}


try {
  $student_id = $user['id'];
  $transaction_desc_id = isset($_POST['transaction_desc_id']) ? $_POST['transaction_desc_id'] : null;
  $from_date = isset($_POST['from_date']) ? $_POST['from_date'] : null;
  $to_date = isset($_POST['to_date']) ? $_POST['to_date'] : null;
  $param = array();

  $sql = "SELECT *, transaction_desc.transaction_description  from transaction 
  LEFT JOIN transaction_desc on transaction_desc.id = transaction_desc_id WHERE student_id = :id AND";


  $param[':id'] = $student_id;

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
