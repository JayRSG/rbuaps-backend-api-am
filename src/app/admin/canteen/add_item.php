<?php

function validate($data)
{
  $expected_keys = ['name', 'price', 'quantity'];

  expect_keys($data, $expected_keys);
}

if (!checkPostMethod()) {
  return;
}

$user = auth();
if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

if (!checkUserType("admin") || $user['admin_type'] != 4) { //canteen salesperson
  response(['message' => "Unauthorized Action"]);
  return;
}

try {
  $name = $_POST['name'] ?? null;
  $price = $_POST['price'] ?? 0;
  $quantity = $_POST['quantity'] ?? 0;

  $sql = "INSERT INTO canteen_prods (name, price, quantity) VALUES(:name, :price, :quantity)";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":name", $name);
  $stmt->bindParam(":price", $price);
  $stmt->bindParam(":quantity", $quantity);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => 'Inserted Successfully'], 200);
  } else {
    response(['message' => 'Insertion Failed'], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
