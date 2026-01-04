<?php
/**
 * 統計クラス
 * 閲覧数、人気記事、アクセス統計
 */

class Statistics {
    private $db;
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    
    /**
     * 人気記事取得（閲覧数順）
     */
    public function getPopularPosts($limit = 10, $days = 30) {
        $dateFrom = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $sql = "SELECT p.*, u.display_name as author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.status = 'published' 
                AND p.created_at >= ?
                ORDER BY p.view_count DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$dateFrom, $limit]);
    }
    
    /**
     * 最近の人気記事（閲覧数が多い）
     */
    public function getTrendingPosts($limit = 5) {
        return $this->getPopularPosts($limit, 7); // 過去7日間
    }
    
    /**
     * カテゴリー別統計
     */
    public function getCategoryStats() {
        $sql = "SELECT c.id, c.name, c.slug, COUNT(pc.post_id) as post_count
                FROM categories c
                LEFT JOIN post_categories pc ON c.id = pc.category_id
                LEFT JOIN posts p ON pc.post_id = p.id AND p.status = 'published'
                GROUP BY c.id, c.name, c.slug
                ORDER BY post_count DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * タグ別統計
     */
    public function getTagStats() {
        $sql = "SELECT t.id, t.name, t.slug, COUNT(pt.post_id) as post_count
                FROM tags t
                LEFT JOIN post_tags pt ON t.id = pt.tag_id
                LEFT JOIN posts p ON pt.post_id = p.id AND p.status = 'published'
                GROUP BY t.id, t.name, t.slug
                ORDER BY post_count DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * 月別投稿統計
     */
    public function getMonthlyStats($months = 12) {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as post_count,
                    SUM(view_count) as total_views
                FROM posts
                WHERE status = 'published'
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY month
                ORDER BY month DESC";
        
        return $this->db->fetchAll($sql, [$months]);
    }
    
    /**
     * 全体統計サマリー
     */
    public function getSummary() {
        // 総投稿数
        $totalPosts = $this->db->fetch("SELECT COUNT(*) as count FROM posts WHERE status = 'published'")['count'];
        
        // 総閲覧数
        $totalViews = $this->db->fetch("SELECT SUM(view_count) as total FROM posts WHERE status = 'published'")['total'] ?? 0;
        
        // 総コメント数
        $totalComments = $this->db->fetch("SELECT COUNT(*) as count FROM comments WHERE status = 'approved'")['count'];
        
        // 総カテゴリー数
        $totalCategories = $this->db->fetch("SELECT COUNT(*) as count FROM categories")['count'];
        
        // 総タグ数
        $totalTags = $this->db->fetch("SELECT COUNT(*) as count FROM tags")['count'];
        
        // 今月の投稿数
        $thisMonthPosts = $this->db->fetch(
            "SELECT COUNT(*) as count FROM posts 
             WHERE status = 'published' 
             AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')"
        )['count'];
        
        return [
            'total_posts' => (int)$totalPosts,
            'total_views' => (int)$totalViews,
            'total_comments' => (int)$totalComments,
            'total_categories' => (int)$totalCategories,
            'total_tags' => (int)$totalTags,
            'this_month_posts' => (int)$thisMonthPosts,
            'avg_views_per_post' => $totalPosts > 0 ? round($totalViews / $totalPosts, 2) : 0
        ];
    }
    
    /**
     * 著者別統計
     */
    public function getAuthorStats() {
        $sql = "SELECT 
                    u.id, u.username, u.display_name,
                    COUNT(p.id) as post_count,
                    SUM(p.view_count) as total_views
                FROM users u
                LEFT JOIN posts p ON u.id = p.author_id AND p.status = 'published'
                GROUP BY u.id, u.username, u.display_name
                HAVING post_count > 0
                ORDER BY post_count DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * 検索キーワードログ（オプション機能）
     */
    public function logSearch($keyword, $results_count) {
        // 検索ログテーブルがある場合
        try {
            $sql = "INSERT INTO search_logs (keyword, results_count, searched_at) 
                    VALUES (?, ?, NOW())";
            $this->db->query($sql, [$keyword, $results_count]);
        } catch (Exception $e) {
            // テーブルがない場合は無視
        }
    }
    
    /**
     * 人気検索キーワード
     */
    public function getPopularSearchKeywords($limit = 10) {
        try {
            $sql = "SELECT keyword, COUNT(*) as search_count
                    FROM search_logs
                    WHERE searched_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY keyword
                    ORDER BY search_count DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$limit]);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
