<?php
/**
 * APIマネージャー
 * 認証とセキュリティ
 */

class APIManager {
    private $db;
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * APIキーの検証
     */
    public function validateKey($key) {
        if (empty($key)) return false;
        
        $sql = "SELECT * FROM api_keys WHERE api_key = ? AND status = 'active'";
        $result = $this->db->fetch($sql, [$key]);
        
        if ($result) {
            // 最終利用日時を更新
            $this->db->query("UPDATE api_keys SET last_used_at = NOW() WHERE id = ?", [$result['id']]);
            return $result;
        }
        
        return false;
    }

    /**
     * APIレスポンス送信 (JSON)
     */
    public static function sendResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * テーブル作成
     */
    public static function createTable($db) {
        if (DB_TYPE === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS api_keys (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                api_key VARCHAR(64) UNIQUE NOT NULL,
                status ENUM('active', 'revoked') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_used_at TIMESTAMP NULL,
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS api_keys (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                name VARCHAR(100) NOT NULL,
                api_key VARCHAR(64) UNIQUE NOT NULL,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_used_at TIMESTAMP NULL
            );
            CREATE INDEX IF NOT EXISTS idx_apikeys_user ON api_keys(user_id)";
        }
        $db->query($sql);
    }
}
?>
