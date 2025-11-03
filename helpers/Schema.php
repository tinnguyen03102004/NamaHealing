<?php

if (!function_exists('ensure_users_has_vip')) {
    function ensure_users_has_vip(PDO $db): void {
        try {
            $db->query('SELECT is_vip FROM users LIMIT 1');
        } catch (PDOException $e) {
            try {
                $db->exec("ALTER TABLE users ADD COLUMN is_vip TINYINT(1) NOT NULL DEFAULT 0 AFTER remaining");
            } catch (PDOException $ignored) {
                // Column may already exist or table might not allow alteration.
            }
        }
    }
}

if (!function_exists('ensure_users_has_first_session_flag')) {
    function ensure_users_has_first_session_flag(PDO $db): void {
        try {
            $db->query('SELECT first_session_completed FROM users LIMIT 1');
        } catch (PDOException $e) {
            $alterStatements = [
                "ALTER TABLE users ADD COLUMN first_session_completed TINYINT(1) NOT NULL DEFAULT 0 AFTER is_vip",
                "ALTER TABLE users ADD COLUMN first_session_completed TINYINT(1) NOT NULL DEFAULT 0 AFTER remaining",
            ];

            foreach ($alterStatements as $sql) {
                try {
                    $db->exec($sql);
                    break;
                } catch (PDOException $ignored) {
                    // Column may already exist or the referenced column might not be available yet.
                }
            }
        }
    }
}

if (!function_exists('ensure_zoom_links_audience')) {
    function ensure_zoom_links_audience(PDO $db): void {
        try {
            $db->query('SELECT audience FROM zoom_links LIMIT 1');
        } catch (PDOException $e) {
            try {
                $db->exec("ALTER TABLE zoom_links ADD COLUMN audience VARCHAR(10) NOT NULL DEFAULT 'student' AFTER session");
            } catch (PDOException $ignored) {
                // Column may already exist or table might not allow alteration.
            }
        }

        $primaryColumns = null;
        try {
            $stmt = $db->query(
                "SELECT GROUP_CONCAT(column_name ORDER BY seq_in_index SEPARATOR ',') " .
                "FROM information_schema.statistics " .
                "WHERE table_schema = DATABASE() AND table_name = 'zoom_links' AND index_name = 'PRIMARY'"
            );
            $primaryColumns = $stmt->fetchColumn();
        } catch (PDOException $ignored) {
            // Ignore metadata lookup failures.
        }

        if ($primaryColumns !== 'session,audience') {
            try {
                $db->exec('ALTER TABLE zoom_links DROP PRIMARY KEY');
            } catch (PDOException $ignored) {
                // Primary key might not exist or already be composite.
            }

            try {
                $db->exec('ALTER TABLE zoom_links ADD PRIMARY KEY (session, audience)');
            } catch (PDOException $ignored) {
                // Composite primary key may already exist.
            }
        }
    }
}

if (!function_exists('ensure_referrals_table')) {
    function ensure_referrals_table(PDO $db): void {
        try {
            $db->query('SELECT 1 FROM referrals LIMIT 1');
            return;
        } catch (PDOException $e) {
            $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL,
    status ENUM('pending','awarded') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    awarded_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uniq_referred (referred_id),
    KEY idx_referrer (referrer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
            try {
                $db->exec($sql);
            } catch (PDOException $ignored) {
                // Table creation might fail if permissions are restricted.
            }
        }
    }
}
