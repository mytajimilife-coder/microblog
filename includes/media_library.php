<?php
/**
 * メディアライブラリ
 * 画像・ファイル管理システム
 */

class MediaLibrary {
    private $db;
    private $uploadPath;
    
    public function __construct($pdo, $uploadPath = 'uploads/') {
        $this->db = $pdo;
        $this->uploadPath = $uploadPath;
    }
    
    /**
     * メディアファイル登録
     */
    public function addMedia($fileData) {
        $sql = "INSERT INTO media (
                    filename, original_name, file_path, file_type, 
                    file_size, mime_type, width, height, 
                    thumbnail_path, uploaded_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $fileData['filename'],
            $fileData['original_name'],
            $fileData['file_path'],
            $fileData['file_type'] ?? 'image',
            $fileData['file_size'],
            $fileData['mime_type'],
            $fileData['width'] ?? null,
            $fileData['height'] ?? null,
            $fileData['thumbnail_path'] ?? null,
            $fileData['uploaded_by'] ?? null
        ];
        
        try {
            $this->db->query($sql, $params);
            return $this->db->connection->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * メディア一覧取得
     */
    public function getMedia($page = 1, $perPage = 20, $type = null) {
        $offset = ($page - 1) * $perPage;
        
        $where = '';
        $params = [];
        
        if ($type !== null) {
            $where = 'WHERE file_type = ?';
            $params[] = $type;
        }
        
        // 総数取得
        $countSql = "SELECT COUNT(*) as total FROM media {$where}";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        // データ取得
        $sql = "SELECT m.*, u.display_name as uploader_name
                FROM media m
                LEFT JOIN users u ON m.uploaded_by = u.id
                {$where}
                ORDER BY m.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $data = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => (int)$total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * メディア検索
     */
    public function search($keyword, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $searchTerm = '%' . $keyword . '%';
        
        $countSql = "SELECT COUNT(*) as total FROM media 
                     WHERE original_name LIKE ? OR filename LIKE ?";
        $total = $this->db->fetch($countSql, [$searchTerm, $searchTerm])['total'];
        
        $sql = "SELECT m.*, u.display_name as uploader_name
                FROM media m
                LEFT JOIN users u ON m.uploaded_by = u.id
                WHERE m.original_name LIKE ? OR m.filename LIKE ?
                ORDER BY m.created_at DESC
                LIMIT ? OFFSET ?";
        
        $data = $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $perPage, $offset]);
        
        return [
            'data' => $data,
            'total' => (int)$total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * メディア削除
     */
    public function deleteMedia($id) {
        // メディア情報取得
        $media = $this->db->fetch("SELECT * FROM media WHERE id = ?", [$id]);
        
        if (!$media) {
            return ['success' => false, 'message' => 'メディアが見つかりません'];
        }
        
        // ファイル削除
        if (file_exists($media['file_path'])) {
            @unlink($media['file_path']);
        }
        
        // サムネイル削除
        if ($media['thumbnail_path'] && file_exists($media['thumbnail_path'])) {
            @unlink($media['thumbnail_path']);
        }
        
        // DB削除
        $this->db->query("DELETE FROM media WHERE id = ?", [$id]);
        
        return ['success' => true, 'message' => 'メディアを削除しました'];
    }
    
    /**
     * メディア情報取得
     */
    public function getMediaById($id) {
        return $this->db->fetch("SELECT * FROM media WHERE id = ?", [$id]);
    }
    
    /**
     * 使用されていないメディア取得
     */
    public function getUnusedMedia() {
        $sql = "SELECT m.* FROM media m
                LEFT JOIN posts p ON m.file_path = p.featured_image
                WHERE p.id IS NULL
                ORDER BY m.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * メディア統計
     */
    public function getStats() {
        $total = $this->db->fetch("SELECT COUNT(*) as count FROM media")['count'];
        $totalSize = $this->db->fetch("SELECT SUM(file_size) as size FROM media")['size'] ?? 0;
        
        $byType = $this->db->fetchAll(
            "SELECT file_type, COUNT(*) as count, SUM(file_size) as size 
             FROM media 
             GROUP BY file_type"
        );
        
        return [
            'total_files' => (int)$total,
            'total_size' => (int)$totalSize,
            'total_size_formatted' => FileHelper::formatFileSize($totalSize),
            'by_type' => $byType
        ];
    }
    
    /**
     * メディアテーブル作成（インストール時）
     */
    public static function createTable($db) {
        if (DB_TYPE === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS media (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_type VARCHAR(50) DEFAULT 'image',
                file_size INT NOT NULL,
                mime_type VARCHAR(100),
                width INT,
                height INT,
                thumbnail_path VARCHAR(500),
                uploaded_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_file_type (file_type),
                INDEX idx_uploaded_by (uploaded_by),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS media (
                id SERIAL PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_type VARCHAR(50) DEFAULT 'image',
                file_size INTEGER NOT NULL,
                mime_type VARCHAR(100),
                width INTEGER,
                height INTEGER,
                thumbnail_path VARCHAR(500),
                uploaded_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE INDEX IF NOT EXISTS idx_media_file_type ON media(file_type);
            CREATE INDEX IF NOT EXISTS idx_media_uploaded_by ON media(uploaded_by);
            CREATE INDEX IF NOT EXISTS idx_media_created_at ON media(created_at)";
        }
        
        try {
            $db->query($sql);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
