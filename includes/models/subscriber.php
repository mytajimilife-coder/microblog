<?php
/**
 * 購読者モデル
 * ニュースレター購読者の管理
 */

class Subscriber {
    private $db;
    private $table = 'subscribers';
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    
    /**
     * テーブル作成
     */
    public static function createTable($db) {
        $sql = "CREATE TABLE IF NOT EXISTS subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) UNIQUE NOT NULL,
            status ENUM('active', 'unsubscribed') DEFAULT 'active',
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $db->query($sql);
    }
    
    /**
     * 購読登録
     */
    public function subscribe($email, $ip = null, $userAgent = null) {
        $sql = "INSERT INTO {$this->table} (email, ip_address, user_agent) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE status = 'active', updated_at = CURRENT_TIMESTAMP";
        
        return $this->db->query($sql, [$email, $ip, $userAgent]);
    }
    
    /**
     * 購読解除
     */
    public function unsubscribe($email) {
        $sql = "UPDATE {$this->table} SET status = 'unsubscribed' WHERE email = ?";
        return $this->db->query($sql, [$email]);
    }
    
    /**
     * 全購読者取得
     */
    public function getAll($status = 'active') {
        $sql = "SELECT * FROM {$this->table} WHERE status = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$status]);
    }
    
    /**
     * 購読者数取得
     */
    public function getCount($status = 'active') {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?";
        $result = $this->db->fetch($sql, [$status]);
        return $result['count'] ?? 0;
    }
}
