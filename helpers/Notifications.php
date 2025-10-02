<?php
declare(strict_types=1);

const NOTIFICATION_TYPES = ['general', 'cancellation'];
const NOTIFICATION_SESSION_SCOPES = ['both', 'morning', 'evening'];

function notifications_setup(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) DEFAULT NULL,
        message TEXT NOT NULL,
        type ENUM('general','cancellation') DEFAULT 'general',
        session_scope ENUM('both','morning','evening') DEFAULT 'both',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $columnsStmt = $db->query('SHOW COLUMNS FROM notifications');
    $columns = $columnsStmt ? $columnsStmt->fetchAll(PDO::FETCH_COLUMN) : [];
    $columns = is_array($columns) ? $columns : [];

    $required = [
        'title'         => "ALTER TABLE notifications ADD COLUMN title VARCHAR(255) DEFAULT NULL AFTER id",
        'type'          => "ALTER TABLE notifications ADD COLUMN type ENUM('general','cancellation') DEFAULT 'general' AFTER message",
        'session_scope' => "ALTER TABLE notifications ADD COLUMN session_scope ENUM('both','morning','evening') DEFAULT 'both' AFTER type",
        'expires_at'    => "ALTER TABLE notifications ADD COLUMN expires_at DATETIME DEFAULT NULL AFTER created_at",
    ];

    foreach ($required as $column => $sql) {
        if (!in_array($column, $columns, true)) {
            $db->exec($sql);
        }
    }

    $db->exec("CREATE TABLE IF NOT EXISTS notification_reads (
        notification_id INT NOT NULL,
        user_id INT NOT NULL,
        read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (notification_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function notifications_create(PDO $db, string $message, array $options = []): int
{
    notifications_setup($db);
    $title = trim($options['title'] ?? '');
    $type = $options['type'] ?? 'general';
    if (!in_array($type, NOTIFICATION_TYPES, true)) {
        $type = 'general';
    }
    $scope = $options['session_scope'] ?? 'both';
    if (!in_array($scope, NOTIFICATION_SESSION_SCOPES, true)) {
        $scope = 'both';
    }
    $expiresAt = null;
    $rawExpires = $options['expires_at'] ?? null;
    if (is_string($rawExpires) && $rawExpires !== '') {
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $rawExpires) ?: DateTime::createFromFormat('Y-m-d\TH:i', $rawExpires);
        if ($dt instanceof DateTime) {
            $expiresAt = $dt->format('Y-m-d H:i:s');
        }
    }

    $stmt = $db->prepare('INSERT INTO notifications (title, message, type, session_scope, expires_at) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$title !== '' ? $title : null, $message, $type, $scope, $expiresAt]);
    return (int)$db->lastInsertId();
}

function notifications_fetch_active(PDO $db, ?string $sessionScope = null): array
{
    notifications_setup($db);
    $sql = "SELECT id, title, message, type, session_scope, created_at, expires_at
            FROM notifications
            WHERE expires_at IS NULL OR expires_at >= NOW()";
    $params = [];
    if ($sessionScope && in_array($sessionScope, NOTIFICATION_SESSION_SCOPES, true) && $sessionScope !== 'both') {
        $sql .= " AND (session_scope = 'both' OR session_scope = ?)";
        $params[] = $sessionScope;
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function notifications_fetch_recent(PDO $db, int $limit = 20): array
{
    notifications_setup($db);
    $stmt = $db->prepare("SELECT id, title, message, type, session_scope, created_at, expires_at,
        (expires_at IS NOT NULL AND expires_at < NOW()) AS is_expired
        FROM notifications
        ORDER BY created_at DESC
        LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function notifications_unread_count(PDO $db, int $userId, ?string $sessionScope = null): int
{
    notifications_setup($db);
    $sql = "SELECT COUNT(*) FROM notifications n
        LEFT JOIN notification_reads r ON n.id = r.notification_id AND r.user_id = :uid
        WHERE (n.expires_at IS NULL OR n.expires_at >= NOW())";
    $params = [':uid' => $userId];
    if ($sessionScope && in_array($sessionScope, NOTIFICATION_SESSION_SCOPES, true) && $sessionScope !== 'both') {
        $sql .= " AND (n.session_scope = 'both' OR n.session_scope = :scope)";
        $params[':scope'] = $sessionScope;
    }
    $sql .= " AND r.notification_id IS NULL";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function notifications_unread_cancellation(PDO $db, int $userId, ?string $sessionScope = null): ?array
{
    notifications_setup($db);
    $sql = "SELECT n.id, n.title, n.message, n.type, n.session_scope, n.created_at
        FROM notifications n
        LEFT JOIN notification_reads r ON n.id = r.notification_id AND r.user_id = :uid
        WHERE n.type = 'cancellation'
          AND (n.expires_at IS NULL OR n.expires_at >= NOW())
          AND r.notification_id IS NULL";
    $params = [':uid' => $userId];
    if ($sessionScope && in_array($sessionScope, NOTIFICATION_SESSION_SCOPES, true) && $sessionScope !== 'both') {
        $sql .= " AND (n.session_scope = 'both' OR n.session_scope = :scope)";
        $params[':scope'] = $sessionScope;
    }
    $sql .= " ORDER BY n.created_at DESC LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function notifications_mark(PDO $db, int $userId, array $notificationIds): void
{
    notifications_setup($db);
    if (empty($notificationIds)) {
        return;
    }
    $stmt = $db->prepare('INSERT IGNORE INTO notification_reads (notification_id, user_id) VALUES (?, ?)');
    foreach ($notificationIds as $id) {
        if (!is_numeric($id)) {
            continue;
        }
        $stmt->execute([(int)$id, $userId]);
    }
}

function notifications_mark_all(PDO $db, int $userId): void
{
    notifications_setup($db);
    $stmt = $db->prepare('SELECT id FROM notifications WHERE expires_at IS NULL OR expires_at >= NOW()');
    $stmt->execute();
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if ($ids) {
        notifications_mark($db, $userId, array_map('intval', $ids));
    }
}

function notifications_delete(PDO $db, int $notificationId): void
{
    notifications_setup($db);
    $stmt = $db->prepare('DELETE FROM notifications WHERE id = ? LIMIT 1');
    $stmt->execute([$notificationId]);
    $stmt = $db->prepare('DELETE FROM notification_reads WHERE notification_id = ?');
    $stmt->execute([$notificationId]);
}
