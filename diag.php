<?php
require __DIR__.'/config.php';

echo "PHP version: ".PHP_VERSION."<br>";
echo "Dotenv loaded: ".(class_exists('Dotenv\\Dotenv') ? 'YES' : 'NO')."<br>";
echo "DB_NAME from ENV: ".($_ENV['DB_NAME'] ?? 'NULL')."<br>";

$_SESSION['ping'] = ($_SESSION['ping'] ?? 0) + 1;
echo "Session counter: ".$_SESSION['ping']."<br>";

try {
  $cnt = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
  echo "Users in DB: ".$cnt."<br>";
} catch (Throwable $e) {
  echo "DB error: ".$e->getMessage()."<br>";
}
