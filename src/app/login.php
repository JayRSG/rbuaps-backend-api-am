<?php
function login_validator($data, $user_type)
{
  $expected_keys = ['email', 'password'];
  if(expect_keys($data, $expected_keys)){
    if($user_type == "admin" || $user_type == "student"){
      return true;
    }else{
      return false;
    }
  }
}


/**
 * Login Method
 */

if (!checkPostMethod()) {
  return;
}

if (auth()) {
  response(['message' => 'Already Logged in'], 400);
  return;
}

// validate inputs
$user_type = isset($_GET['u_t']) ? $_GET['u_t'] : null;
if (!login_validator($_POST, $user_type)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $email = $_POST['email'] ?? null;
  $password = $_POST['password'] ?? null;

  $sql = "SELECT * FROM $user_type WHERE email = :email LIMIT 1";
  $stmt = $conn->prepare($sql);

  if ($email) {
    $stmt->bindParam(':email', $email);
  }

  $result = $stmt->execute();

  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user) {
    // User found, check the password

    if ($user['active'] == 0) {
      response(['message' => "Access Denied"], 401);
      return;
    }

    if (password_verify($password, $user['password'])) {
      // Password or fingerprint matches, allow login
      $_SESSION['auth'] = $user;
      $_SESSION['auth_type'] = $user_type;
      response(['message' => "Login Successful"], 200);
    } else {
      // Password does not match
      response(["message" => "Invalid credentials"], 401);
    }
  } else {
    // User not found
    response(["message" => "User not found"], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
