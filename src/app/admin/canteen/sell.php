<?php
require_once __DIR__ . "/../../user/card_payment_management.php";
require_once __DIR__ . "/../../user/make_transaction.php";

function validate($data)
{
  $expected_keys = ['items_cart', 'student_id', 'rfid'];

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

if (!checkUserType("admin") || $user['admin_type'] != 4) {
  //canteen salesperson
  response(['message' => "Unauthorized Action"]);
  return;
}


try {
  $prods = $_POST['items_cart'];

  $student_id = $_POST['student_id'];
  $admin_id = $user['id'];
  $rfid = $_POST['rfid'];
  $transaction_desc_id = 3; //Canteen Products from transaction_desc
  $amount = 0;

  $transaction_id = 1;
  $item_ids = "";
  $item_quantity = [];
  $selected_canteen_prods = [];

  // Extract Cart items and get id and quantity separately
  foreach ($prods as $key) {
    foreach ($key as $key => $value) {
      if ($key == "item_id") {
        $item_ids .= "$value, ";
      }

      if ($key == "quantity") {
        array_push($item_quantity, $value);
      }
    }
  }

  $item_ids = rtrim($item_ids, ', ');
  $item_ids = "( $item_ids )";

  // Find the item details (price) from canteen_prod table 
  $sql = "SELECT id, price, quantity as stock_qty from canteen_prods WHERE id IN $item_ids";
  $stmt = $conn->prepare($sql);
  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $selected_canteen_prods = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Calculate total payable amount
  $i = 0;
  foreach ($selected_canteen_prods as $key => $value) {
    foreach ($value as $key => $value) {
      if ($key == "price") {
        $amount += $item_quantity[$i++] * $value;
      }
    }
  }

  $card_balance = verify_account_info($conn, $student_id, $rfid, $amount);
  // Check if account has sufficient funds
  if (!$card_balance) {
    return;
  }

  // Deducts balance from the card  
  if (!deduct_account_balance($conn, $student_id, $rfid, $amount)) {
    return;
  }

  if (!make_transaction($conn, $student_id, $amount, $transaction_desc_id, $admin_id)) {
    response(['message' => 'Transaction Failed'], 400);
    return;
  }

  $transaction_id = $conn->lastInsertId();


  $sql = "INSERT INTO trans_canteen_rel (transaction_id, canteen_prod_id, quantity, price) VALUES ";

  for ($i = 0; $i < count($prods); $i++) {
    if ($selected_canteen_prods[$i]['id'] == $prods[$i]['item_id']) {
      $item_id = $prods[$i]['item_id'];
      $qty = $prods[$i]['quantity'];
      $price = $selected_canteen_prods[$i]['price'];

      $sql .= "($transaction_id, $item_id, $qty, $price), ";
    }
  }
  $sql = rtrim($sql, ', ');
  
  $stmt = $conn->prepare($sql);
  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => "Payment Successful"], 200);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
