<?php
/**
 * セキュリティ・監査ログクラス
 * ログイン試行制限、アクティビティログ
 */

class SecurityAudit {
    private $db;
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    
    /**
     * ログイン試行を記録
     */
    public function recordLoginAttempt($username, $ip, $success) {
        $sql = "INSERT INTO login_attempts (username, ip_address, success, attempted_at) 
                VALUES (?, ?, ?, NOW())";
        try {
            $this->db->query($sql, [$username, $ip, $success ? 1 : 0]);
        } catch (Exception $e) {
            // テーブルがない場合は無視
        }
    }
    
    /**
     * ブルートフォースチェック（短時間のリトライ制限）
     */
    public function isBruteForce($username, $ip, $limit = 5, $minutes = 15) {
        $sql = "SELECT COUNT(*) as count FROM login_attempts 
                WHERE (username = ? OR ip_address = ?) 
                AND success = 0 
                AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        
        try {
            $result = $this->db->fetch($sql, [$username, $ip, $minutes]);
            return ($result['count'] >= $limit);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * アクティビティログを記録
     */
    public function logActivity($userId, $action, $details = '') {
        $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        try {
            $this->db->query($sql, [
                $userId, 
                $action, 
                $details, 
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
        } catch (Exception $e) {
            // テーブルがない場合は無視
        }
    }
    
    /**
     * セキュリティヘッダーの設定
     */
    public static function setSecurityHeaders() {
        if (headers_sent()) return;
        
        // XSS保護
        header("X-XSS-Protection: 1; mode=block");
        // クリックジャッキング対策
        header("X-Frame-Options: SAMEORIGIN");
        // MIMEタイプ・スニッフィング対策
        header("X-Content-Type-Options: nosniff");
        // リファラー制御
        header("Referrer-Policy: strict-origin-when-cross-origin");
        // HSTS (HTTPS環境のみ推奨)
        // header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
    
    /**
     * テーブル作成
     */
    public static function createTables($db) {
        if (DB_TYPE === 'mysql') {
            $queries = [
                "CREATE TABLE IF NOT EXISTS login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255),
                    ip_address VARCHAR(45),
                    success TINYINT(1),
                    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ip_time (ip_address, attempted_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                
                "CREATE TABLE IF NOT EXISTS activity_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    action VARCHAR(100),
                    details TEXT,
                    ip_address VARCHAR(45),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_time (user_id, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            ];
        } else {
            $queries = [
                "CREATE TABLE IF NOT EXISTS login_attempts (
                    id SERIAL PRIMARY KEY,
                    username VARCHAR(255),
                    ip_address VARCHAR(45),
                    success SMALLINT,
                    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE INDEX IF NOT EXISTS idx_la_ip_time ON login_attempts(ip_address, attempted_at)",
                
                "CREATE TABLE IF NOT EXISTS activity_logs (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER,
                    action VARCHAR(100),
                    details TEXT,
                    ip_address VARCHAR(45),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE INDEX IF NOT EXISTS idx_al_user_time ON activity_logs(user_id, created_at)"
            ];
        }
        
        foreach ($queries as $sql) {
            $db->query($sql);
        }
    }
}
?>
