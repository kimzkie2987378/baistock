<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('mysql:host=127.0.0.1;port=3309;dbname=baistock_db', 'baistock_user', 'baistock_pass');
$adminHash = password_hash('kimzkie0978', PASSWORD_BCRYPT);
$staffHash = password_hash('kimzkie@0978', PASSWORD_BCRYPT);

$pdo->prepare("UPDATE users SET password = ? WHERE id = 1")->execute([$adminHash]);
$pdo->prepare("UPDATE users SET username = 'staff1', email = 'staff1@gmail.com', password = ? WHERE id = 2")->execute([$staffHash]);
$pdo->prepare("UPDATE products SET quantity = 20 WHERE id = 5")->execute();

$users = $pdo->query("SELECT id, username, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);
