<?php
declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use helpers\Mailer;
use PDO;

// Nạp env
if (class_exists(\Dotenv\Dotenv::class)) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST'), getenv('DB_NAME'));
$pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$limit = 20;
$pdo->beginTransaction();
$stmt = $pdo->prepare("SELECT * FROM email_queue WHERE status='pending' ORDER BY id ASC LIMIT :lim FOR UPDATE");
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pdo->commit();

foreach ($rows as $row) {
    $ok = false; $err = null;
    try { $ok = Mailer::sendNow($row); }
    catch (Throwable $e) { $ok = false; $err = $e->getMessage(); }

    $sql = "UPDATE email_queue SET status=:st, attempts=attempts+1, last_error=:err WHERE id=:id";
    $upd = $pdo->prepare($sql);
    $upd->execute([
        ':st' => $ok ? 'sent' : 'failed',
        ':err' => $ok ? null : mb_substr((string)$err,0,255),
        ':id' => $row['id'],
    ]);
}
