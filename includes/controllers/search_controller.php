<?php
/**
 * 検索コントローラー
 */
class SearchController {
    private $db;
    private $postModel;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->postModel = new Post($pdo);
    }
    
    /**
     * 検索実行
     */
    public function search($query, $page = 1) {
        $perPage = POSTS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        // 検索クエリのサニタイズ
        $searchTerm = '%' . $query . '%';
        
        // 検索SQL
        $sql = "SELECT * FROM posts 
                WHERE status = 'published' 
                AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        
        $posts = $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $perPage, $offset]);
        
        // 総件数取得
        $countSql = "SELECT COUNT(*) as total FROM posts 
                     WHERE status = 'published' 
                     AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
        $totalResult = $this->db->fetch($countSql, [$searchTerm, $searchTerm, $searchTerm]);
        $total = (int) $totalResult['total'];
        
        $totalPages = ceil($total / $perPage);
        
        // テンプレート読み込み
        $theme = DEFAULT_THEME;
        include "themes/{$theme}/header.php";
        include "themes/{$theme}/search.php";
        include "themes/{$theme}/footer.php";
    }
}
?>
