<?php
/**
 * 投稿メタデータ管理
 * カスタムフィールドの保存と取得
 */

class PostMeta {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * メタデータを取得
     */
    public function get($postId, $key, $single = true) {
        $sql = "SELECT meta_value FROM post_meta WHERE post_id = ? AND meta_key = ?";
        if ($single) {
            $result = $this->db->fetch($sql, [$postId, $key]);
            return $result ? unserialize($result['meta_value']) : null;
        } else {
            $results = $this->db->fetchAll($sql, [$postId, $key]);
            return array_map(function($r) { return unserialize($r['meta_value']); }, $results);
        }
    }

    /**
     * メタデータを保存（更新または挿入）
     */
    public function update($postId, $key, $value) {
        $serializedValue = serialize($value);
        
        if (DB_TYPE === 'mysql') {
            $sql = "INSERT INTO post_meta (post_id, meta_key, meta_value) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";
        } else {
            $sql = "INSERT INTO post_meta (post_id, meta_key, meta_value) 
                    VALUES (?, ?, ?) 
                    ON CONFLICT (post_id, meta_key) DO UPDATE SET meta_value = EXCLUDED.meta_value";
        }

        return $this->db->query($sql, [$postId, $key, $serializedValue]);
    }

    /**
     * メタデータを削除
     */
    public function delete($postId, $key = null) {
        $sql = "DELETE FROM post_meta WHERE post_id = ?";
        $params = [$postId];
        
        if ($key !== null) {
            $sql .= " AND meta_key = ?";
            $params[] = $key;
        }
        
        return $this->db->query($sql, $params);
    }

    /**
     * テーブル作成
     */
    public static function createTable($db) {
        if (DB_TYPE === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS post_meta (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                meta_key VARCHAR(255) NOT NULL,
                meta_value LONGTEXT,
                UNIQUE KEY unique_post_meta (post_id, meta_key),
                INDEX idx_post_id (post_id),
                INDEX idx_meta_key (meta_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS post_meta (
                id SERIAL PRIMARY KEY,
                post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
                meta_key VARCHAR(255) NOT NULL,
                meta_value TEXT,
                UNIQUE (post_id, meta_key)
            );
            CREATE INDEX IF NOT EXISTS idx_postmeta_post ON post_meta(post_id);
            CREATE INDEX IF NOT EXISTS idx_postmeta_key ON post_meta(meta_key)";
        }
        $db->query($sql);
    }
}
?>
