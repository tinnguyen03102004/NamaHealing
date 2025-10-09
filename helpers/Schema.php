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
