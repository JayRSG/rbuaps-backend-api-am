<?php

function validate($data)
{
  if (empty($data['id']) && empty($data['all'])) {
    return false;
  } else {
    return true;
  }
}

if (!checkGetMethod()) {
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

if (!validate($_POST)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $all = $_POST['all'] ?? null;
  $id = $_POST['id'] ?? null;

  $sql = "";

  if ($all) {
    $sql = "SELECT * from canteen_prods";
  } else if ($id > 0) {
    $sql = "SELECT * from canteen_prods WHERE id = :id";
  }

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":id", $id);
  $result = $stmt->execute();


  if ($result && $stmt->rowCount() > 0) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($data) {
      response(['data' => $data], 200);
    } else {
      response(['message' => 'Not Found'], 404);
    }
  } else {
    response(['message' => 'Not Found'], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
