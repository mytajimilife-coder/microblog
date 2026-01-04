<?php
/**
 * コメントモデル
 */
class Comment extends BaseModel {
    protected $table = 'comments';
    protected $fillable = ['post_id', 'parent_id', 'author_name', 'author_email', 'author_url', 'author_ip', 'content', 'status'];
    
    /**
     * 投稿のコメント取得
     */
    public function getByPost($postId, $status = 'approved') {
        $sql = "SELECT * FROM {$this->table} 
                WHERE post_id = ? AND status = ? AND parent_id IS NULL
                ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$postId, $status]);
    }
    
    /**
     * 返信コメント取得
     */
    public function getReplies($commentId, $status = 'approved') {
        $sql = "SELECT * FROM {$this->table} 
                WHERE parent_id = ? AND status = ?
                ORDER BY created_at ASC";
        return $this->db->fetchAll($sql, [$commentId, $status]);
    }
    
    /**
     * コメント数取得
     */
    public function countByPost($postId, $status = 'approved') {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE post_id = ? AND status = ?";
        $result = $this->db->fetch($sql, [$postId, $status]);
        return (int) $result['total'];
    }
    
    /**
     * 承認待ちコメント数
     */
    public function countPending() {
        return $this->count(['status' => 'pending']);
    }
    
    /**
     * コメント承認
     */
    public function approve($id) {
        return $this->update($id, ['status' => 'approved']);
    }
    
    /**
     * コメントをスパムに
     */
    public function markAsSpam($id) {
        return $this->update($id, ['status' => 'spam']);
    }
    
    /**
     * 最近のコメント取得
     */
    public function getRecent($limit = 10, $status = 'approved') {
        $sql = "SELECT c.*, p.title as post_title, p.slug as post_slug
                FROM {$this->table} c
                LEFT JOIN posts p ON c.post_id = p.id
                WHERE c.status = ?
                ORDER BY c.created_at DESC
                LIMIT ?";
        return $this->db->fetchAll($sql, [$status, $limit]);
    }
}
?>
