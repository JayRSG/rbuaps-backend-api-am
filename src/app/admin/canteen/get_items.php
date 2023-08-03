<?php

function validate($data)
{
  if (!isset($data['search_term']) && !isset($data['all']) && !isset($data['id'])) {
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

if (!validate($_GET)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $all = !empty($_GET['all']) ? $_GET['all'] :  null;
  $search_term = $_GET['search_term'] ?? null;
  $id = !empty($_GET['id']) ? $_GET['id'] : null;

  $sql = "";

  if ($all || $id) {
    $sql = "SELECT * from canteen_prods";
  } else if (!empty($search_term)) {
    $sql = "SELECT * from canteen_prods WHERE name LIKE  :search_term OR";
  }

  if ($id) {
    $sql .= " WHERE id = :id";
  }

  $sql = rtrim($sql, " OR");

  $stmt = $conn->prepare($sql);

  if ($search_term) {
    $stmt->bindValue(":search_term", "%" . $search_term . "%");
  }

  if ($id) {
    $stmt->bindValue(":id", $id);
  }

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
  response(['message' => $e->getMessage(), $sql], 500);
}
