<?php
return [
  'db' => [
    'host' => 'database-rookiesphoto.crc0gq2e6kq5.ap-northeast-2.rds.amazonaws.com',
    'name' => 'rookiesphoto',
    'user' => 'root',
    'pass' => 'rookiesphoto',
    'charset' => 'utf8mb4',
  ],
  'app_key' => 'base64:' . base64_encode(random_bytes(32)), // ¾Û ºñ¹ÐÅ°
];
