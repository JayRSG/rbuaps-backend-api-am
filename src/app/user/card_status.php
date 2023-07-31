<?php
function validate($data)
{
  $expected_keys = ['student_id'];
  return expect_keys($data, $expected_keys);
}

if (!checkPostMethod()) {
  return;
}

$user = auth();

if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}



if ((auth_type() != "admin" && $user['admin_type'] != 2) || (auth_type() != "student")) {
  response(['message' => "Unauthorized"], 401);
  return;
}


if (auth_type() == "admin" && $user['admin_type'] == 2 && !validate($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}

try {
  $student_id = $_POST['student_id'] ?? null;

  if (auth_type() == "admin") {
    $sql = "SELECT * from card_status WHERE student_id = (SELECT id from student WHERE student_id = :student_id)";
  } else if (auth_type() == "student") {
    $student_id = $user['id'];
    $sql = "SELECT * from card_status WHERE student_id = :student_id";
  }
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":student_id", $student_id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $card_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

    response(['data' => $card_status]);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
