<?php

function validator($data)
{
  $expected_keys = ['user_type', 'id'];

  return expect_keys($data, $expected_keys);
}

$user = auth();
if (!$user) {
  response(['message' => 'Unauthenticated'], 401);
  return;
}
if (!checkUserType('admin')) {
  response(['message' => 'Unauthorized Access'], 401);
  return;
}

// response(validator($_POST) ? "yes" : "no");
// return;


if (!validator($_POST)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $user_type = $_POST['user_type'] ?? null;
  $id = $_POST['id'] ?? null;
  $email = $_POST['email'] ?? null;
  $phone = $_POST['phone'] ?? null;
  $first_name = $_POST['first_name'] ?? null;
  $last_name = $_POST['last_name'] ?? null;

  $params = array();

  $fields = $user_type == "student" ?
    [
      "$user_type.id",
      'first_name',
      'last_name',
      'fathers_name',
      'mothers_name',
      'email',
      'phone',
      'guardian_phone',
      'student.student_id',
      'active',
      'student.created_at',
      'student.updated_at',
      'card_status.balance'
    ]
    : ($user_type == "admin"  ?
      [
        "$user_type.id",
        'first_name',
        'last_name',
        'email',
        'phone',
        'admin_type',
        'admin_type.admin_type_name',
        'active',
        'created_at',
        'updated_at',
      ]
      : null);

  $sql = "SELECT ";

  foreach ($fields as $value) {
    $sql .= "$value, ";
  }

  $sql = rtrim($sql, ', ');

  $sql .= " FROM $user_type ";

  if ($user_type == "admin") {
    $sql .= "INNER JOIN admin_type on admin_type.id = $user_type.admin_type WHERE";
  } else {
    $sql  .= "
    LEFT JOIN card_status on student.id = card_status.student_id
    WHERE ";
  }

  if ($id) {
    if ($user_type = "student") {
      $sql .= " $user_type.student_id = :id AND";
    } else {
      if ($user_type == "admin") {
        $sql .= " $user_type.id = :id AND";
      }
    }
    $params[":id"] = $id;
  }

  if ($email) {
    $sql .= " $user_type.email = :email AND";
    $params[":email"] = $email;
  }

  if ($phone) {
    $sql .= " $user_type.phone = :phone AND";
    $params[":phone"] = $phone;
  }

  if ($first_name) {
    $sql .= " $user_type.first_name LIKE :first_name AND";
    $params[":first_name"] = $first_name;
  }

  if ($last_name) {
    $sql .= " $user_type.last_name LIKE :last_name AND";
    $params[":last_name"] = $last_name;
  }
  $sql = rtrim($sql, " AND");

  $stmt = $conn->prepare($sql);

  foreach ($params as $key => $value) {
    if ($key == ":first_name" || $key == ":last_name") {
      $stmt->bindValue($key, '%' . $value . '%');
    } else {
      $stmt->bindValue($key, $value);
    }
  }

  $result = $stmt->execute();
  $user = null;

  if ($result && $stmt->rowCount() == 1) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
  } else if ($result && $stmt->rowCount() > 0) {
    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  if ($user) {
    response(['data' => $user], 200);
  } else {
    response(['message' => "Not Found", $sql], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
