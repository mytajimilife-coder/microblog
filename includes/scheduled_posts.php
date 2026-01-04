<?php
/**
 * スケジュール投稿管理
 * 予約投稿の自動公開
 */

class ScheduledPosts {
    private $db;
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    
    /**
     * 予約投稿を公開状態に更新
     */
    public function publishScheduledPosts() {
        $now = date('Y-m-d H:i:s');
        
        $sql = "UPDATE posts 
                SET status = 'published', 
                    published_at = scheduled_publish_at,
                    updated_at = NOW()
                WHERE status = 'scheduled' 
                AND scheduled_publish_at <= ?";
        
        try {
            $stmt = $this->db->query($sql, [$now]);
            $count = $stmt->rowCount();
            
            return [
                'success' => true,
                'published_count' => $count,
                'message' => "{$count}件の予約投稿を公開しました"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'エラー: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 予約投稿一覧取得
     */
    public function getScheduledPosts() {
        $sql = "SELECT p.*, u.display_name as author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.status = 'scheduled'
                ORDER BY p.scheduled_publish_at ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * 予約投稿作成
     */
    public function schedulePost($postData, $scheduledTime) {
        $postData['status'] = 'scheduled';
        $postData['scheduled_publish_at'] = $scheduledTime;
        
        $postModel = new Post($this->db);
        return $postModel->create($postData);
    }
    
    /**
     * 予約投稿キャンセル（下書きに戻す）
     */
    public function cancelSchedule($postId) {
        $sql = "UPDATE posts 
                SET status = 'draft', 
                    scheduled_publish_at = NULL,
                    updated_at = NOW()
                WHERE id = ? AND status = 'scheduled'";
        
        try {
            $this->db->query($sql, [$postId]);
            return ['success' => true, 'message' => '予約をキャンセルしました'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'エラー: ' . $e->getMessage()];
        }
    }
    
    /**
     * 次の予約投稿取得
     */
    public function getNextScheduled() {
        $sql = "SELECT p.*, u.display_name as author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.status = 'scheduled'
                AND p.scheduled_publish_at > NOW()
                ORDER BY p.scheduled_publish_at ASC
                LIMIT 1";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * 予約投稿統計
     */
    public function getStats() {
        $total = $this->db->fetch(
            "SELECT COUNT(*) as count FROM posts WHERE status = 'scheduled'"
        )['count'];
        
        $upcoming = $this->db->fetch(
            "SELECT COUNT(*) as count FROM posts 
             WHERE status = 'scheduled' 
             AND scheduled_publish_at > NOW()"
        )['count'];
        
        $overdue = $this->db->fetch(
            "SELECT COUNT(*) as count FROM posts 
             WHERE status = 'scheduled' 
             AND scheduled_publish_at <= NOW()"
        )['count'];
        
        return [
            'total' => (int)$total,
            'upcoming' => (int)$upcoming,
            'overdue' => (int)$overdue
        ];
    }
}
?>
