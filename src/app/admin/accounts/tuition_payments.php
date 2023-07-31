<?php
require_once __DIR__ . "../../../user/make_transaction.php";
require_once __DIR__ . "../../../user/card_payment_management.php";

function validate($data)
{
  $expected_keys = ['student_id', 'trans_desc_id', 'amount', 'rfid'];

  return expect_keys($data, $expected_keys);
}

if (!checkPostMethod()) {
  return;
}

$user = auth();
if (!$user) {
  response(['message' => 'Unauthenticated'], 200);
  return;
}

if (!checkUserType('admin')) {
  response(['message' => "Unauthorized"], 401);
  return;
}

if ($user['admin_type'] != 2) { //Accounts
  response(['message' => "Unauthorized Access"], 401);
  return;
}


if (!validate($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}

try {
  $student_id = $_POST['student_id'] ?? null;
  $admin_id = $user['id'];
  $rfid = $_POST['rfid'];
  $amount = $_POST['amount'];
  $transaction_desc_id = $_POST['transaction_desc_id'];

  $balance_deducted = false;


  if ($transaction_desc_id != 1 && $transaction_desc_id != 2) {
    response(['message' => 'Bad Request'], 400);
    return;
  }

  $balance = verify_account_info($conn, $student_id, $rfid, $amount);

  if ($balance && $balance >= $amount) {
    if (!deduct_account_balance($conn, $student_id, $rfid, $amount)) {
      return;
    }

    $balance_deducted = true;

    if (!make_transaction($conn, $student_id, $amount, $transaction_desc_id, $admin_id)) {
      refund_account_balance($conn, $student_id, $rfid, $amount);

      response(['message' => "Payment failed"], 400);
      return;
    }

    response(['message' => 'Payment Successful']);
  } else {
    return;
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
