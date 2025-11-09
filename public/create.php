<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/controllers/FreePhotoController.php';

$controller = new FreePhotoController();
$controller->create($pdo);