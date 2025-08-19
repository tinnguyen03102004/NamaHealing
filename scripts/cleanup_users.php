<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
if (!class_exists('NamaHealing\\Models\\UserModel')) {
    spl_autoload_register(function ($class) {
        $prefix = 'NamaHealing\\';
        if (str_starts_with($class, $prefix)) {
            $class = substr($class, strlen($prefix));
        }
        $file = dirname(__DIR__) . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
}

if (class_exists(\Dotenv\Dotenv::class)) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST'), getenv('DB_NAME'));
$pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allowedRoles = ['student', 'teacher', 'admin'];

$stmt = $pdo->query('SELECT id, phone, role FROM users');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    $phone = preg_replace('/\D+/', '', $row['phone'] ?? '');
    $role  = in_array($row['role'], $allowedRoles, true) ? $row['role'] : 'student';
    if ($phone !== ($row['phone'] ?? '') || $role !== $row['role']) {
        $upd = $pdo->prepare('UPDATE users SET phone = :phone, role = :role WHERE id = :id');
        $upd->execute([
            ':phone' => $phone,
            ':role'  => $role,
            ':id'    => $row['id'],
        ]);
    }
}

try {
    $pdo->exec("ALTER TABLE users MODIFY role ENUM('student','teacher','admin') NOT NULL DEFAULT 'student'");
} catch (Throwable $e) {
    fwrite(STDERR, 'WARN: ' . $e->getMessage() . PHP_EOL);
}
