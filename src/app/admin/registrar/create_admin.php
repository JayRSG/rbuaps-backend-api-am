<?php

function validate($data)
{
  $expected_keys = ['first_name', 'last_name', 'email', 'password', 'phone', 'admin_type'];

  return expect_keys($data, $expected_keys);
}

if (!checkPostMethod()) {
  return;
}

$user = auth();
if (!checkUserType("admin") || $user['admin_type'] != 1) {
  response(['message' => "Unauthorized Action"]);
  return;
}

if (!validate($_POST)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $email = $_POST['email'];
  $password = password_hash('12345678', PASSWORD_DEFAULT);
  $phone = $_POST['phone'];
  $admin_type = $_POST['admin_type'];

  $params = [];

  $sql = "INSERT INTO admin (first_name, last_name, email, password, phone, admin_type) VALUES(:first_name, :last_name, :email, :password, :phone, :admin_type)";

  $stmt = $conn->prepare($sql);

  $params = [
    ":first_name" => $first_name,
    ":last_name" => $last_name,
    ":email" => $email,
    ":password" => $password,
    ":phone" => $phone,
    ":admin_type" => $admin_type,
  ];

  foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
  }

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => "User Created"], 200);
  } else {
    response(['message' => "User creation failed"], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
