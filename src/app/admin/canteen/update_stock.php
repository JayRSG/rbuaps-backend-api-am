<?php

function validate($data)
{
  $expected_keys = ['id', 'quantity', 'price'];

  return expect_keys($data, $expected_keys);
}

if (!checkPostMethod()) {
  return;
}

if (!validate($_POST)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

$user = auth();
if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

if (!checkUserType("admin") || $user['admin_type'] != 4) {
  //canteen salesperson
  response(['message' => "Unauthorized Action"]);
  return;
}


try {
  $id = $_POST['id'];
  $price = $_POST['price'];
  $quantity = $_POST['quantity'];

  $sql = "UPDATE canteen_prods SET price = :price, quantity = :quantity WHERE id = :id";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":id", $id);
  $stmt->bindParam(":price", $price);
  $stmt->bindParam(":quantity", $quantity);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => 'Updated Successfully'], 200);
  } else {
    response(['message' => 'Update Failed'], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
