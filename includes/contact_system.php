<?php
/**
 * お問い合わせシステム
 * 訪問者からのメッセージ管理
 */

class ContactSystem {
    private $db;
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    
    /**
     * メッセージ送信
     */
    public function submitMessage($data) {
        // バリデーション
        if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
            return ['success' => false, 'message' => '必須項目を入力してください'];
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => '有効なメールアドレスを入力してください'];
        }
        
        $sql = "INSERT INTO contact_messages (name, email, subject, message, ip_address, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        try {
            $this->db->query($sql, [
                $data['name'],
                $data['email'],
                $data['subject'] ?? 'お問い合わせ',
                $data['message'],
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
            
            return ['success' => true, 'message' => 'メッセージを送信しました。ありがとうございます。'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '送信中にエラーが発生しました'];
        }
    }
    
    /**
     * メッセージ一覧取得（管理用）
     */
    public function getMessages($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?";
        return $this->db->fetchAll($sql, [$limit, $offset]);
    }
    
    /**
     * 未読メッセージ数
     */
    public function getUnreadCount() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
        return (int)$result['count'];
    }
    
    /**
     * 既読にする
     */
    public function markAsRead($id) {
        return $this->db->query("UPDATE contact_messages SET is_read = 1 WHERE id = ?", [$id]);
    }
    
    /**
     * テーブル作成
     */
    public static function createTable($db) {
        if (DB_TYPE === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL,
                subject VARCHAR(200),
                message TEXT NOT NULL,
                is_read TINYINT(1) DEFAULT 0,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL,
                subject VARCHAR(200),
                message TEXT NOT NULL,
                is_read SMALLINT DEFAULT 0,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $db->query($sql);
    }
}
?>
