<?php
if (!checkGetMethod()) {
  return;
}


try {
  $sql = "SELECT * from admin_type";
  $stmt = $conn->prepare($sql);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    response(['data' => $data]);
  } else {
    response(['message' => 'Not Found']);
  }
} catch (PDOException $th) {
  response(['message' => $th->getMessage()], 500);
}
