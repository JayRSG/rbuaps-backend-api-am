<?php

if (!checkGetMethod()) {
  return;
}

$user = auth();
if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}


if ($user) {
  $keys_to_remove = ['password', 'active'];
  $my_profile = array();

  foreach ($user as $key => $value) {
    if (!in_array($key, $keys_to_remove)) {
      $my_profile[$key] = $value;
    }
  }

  response(['data' => $my_profile], 200);
}
