<?php
/**
 * IPアクセス制御システム
 * 特定のIPアドレスからのアクセスを制限または許可
 */

class IPManager {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * 現在のIPがブロックされているかチェック
     */
    public function isBlocked($ip = null) {
        $ip = $ip ?? $this->getClientIP();
        
        $sql = "SELECT id FROM ip_bans 
                WHERE ip_address = ? 
                AND (expires_at IS NULL OR expires_at > NOW())";
        
        return (bool)$this->db->fetch($sql, [$ip]);
    }

    /**
     * IPをブロックリストに追加
     */
    public function block($ip, $reason = '', $durationDays = null) {
        $expiresAt = $durationDays ? date('Y-m-d H:i:s', strtotime("+{$durationDays} days")) : null;
        
        if (DB_TYPE === 'mysql') {
            $sql = "INSERT INTO ip_bans (ip_address, reason, expires_at) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE reason = VALUES(reason), expires_at = VALUES(expires_at)";
        } else {
            $sql = "INSERT INTO ip_bans (ip_address, reason, expires_at) 
                    VALUES (?, ?, ?) 
                    ON CONFLICT (ip_address) DO UPDATE SET reason = EXCLUDED.reason, expires_at = EXCLUDED.expires_at";
        }

        return $this->db->query($sql, [$ip, $reason, $expiresAt]);
    }

    /**
     * クライアントの正確なIPを取得
     */
    private function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * テーブル作成
     */
    public static function createTable($db) {
        if (DB_TYPE === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS ip_bans (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) UNIQUE NOT NULL,
                reason VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS ip_bans (
                id SERIAL PRIMARY KEY,
                ip_address VARCHAR(45) UNIQUE NOT NULL,
                reason VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL
            );
            CREATE INDEX IF NOT EXISTS idx_ipbans_expires ON ip_bans(expires_at)";
        }
        $db->query($sql);
    }
}
?>
