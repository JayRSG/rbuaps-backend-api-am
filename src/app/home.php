<?php

echo "Welcome to RBUAPS v1.0";

echo "<BR/><BR/>";

echo "<pre>";
echo json_encode([
  "data" => [
    "Author" => "Arpita Dhar Moni and Tahsin Mim",
    "Project Name" => "RFID Based University Administration Payments System",
    "Authored Date" => "July 2023",
    "Institute" => "Chittagong Independent University",
  ]
], JSON_PRETTY_PRINT);

echo "</pre>";
