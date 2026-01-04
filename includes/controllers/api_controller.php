<?php
/**
 * REST API コントローラー
 */

class APIController {
    private $db;
    private $postModel;

    public function __construct($pdo) {
        $this->db = $pdo;
        require_once __DIR__ . '/../model.php';
        $this->postModel = new Post($this->db);
    }

    /**
     * 投稿一覧 (GET /api/posts)
     */
    public function getPosts($params) {
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
        
        $results = $this->postModel->getPaginated($page, $limit);
        
        return [
            'status' => 'success',
            'data' => $results['posts'],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $results['total_pages'],
                'total_posts' => $results['total_posts']
            ]
        ];
    }

    /**
     * 投稿詳細 (GET /api/posts/{id})
     */
    public function getPost($id) {
        $post = $this->postModel->getById($id);
        
        if (!$post) {
            return ['status' => 'error', 'message' => '投稿が見つかりません', 'code' => 404];
        }

        // 閲覧数を増やす
        $this->postModel->incrementViews($id);

        return [
            'status' => 'success',
            'data' => $post
        ];
    }

    /**
     * 検索 (GET /api/search?q=...)
     */
    public function search($query) {
        if (empty($query)) {
            return ['status' => 'error', 'message' => '検索クエリが空です', 'code' => 400];
        }

        $sql = "SELECT id, title, excerpt, slug, created_at 
                FROM posts 
                WHERE (title LIKE ? OR content LIKE ?) 
                AND status = 'published' 
                ORDER BY created_at DESC LIMIT 20";
        
        $searchTerm = "%{$query}%";
        $results = $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);

        return [
            'status' => 'success',
            'count' => count($results),
            'data' => $results
        ];
    }

    /**
     * 統計情報 (GET /api/stats)
     */
    public function getStats() {
        require_once __DIR__ . '/../statistics.php';
        $stats = new Statistics($this->db);
        return [
            'status' => 'success',
            'data' => $stats->getSummary()
        ];
    }

    /**
     * 新規投稿作成 (POST /api/posts)
     */
    public function createPost($data, $userId) {
        if (empty($data['title']) || empty($data['content'])) {
            return ['status' => 'error', 'message' => 'タイトルと本文は必須です', 'code' => 400];
        }

        require_once __DIR__ . '/../seo_helper.php';
        
        $title = $data['title'];
        $content = $data['content'];
        $status = $data['status'] ?? 'published';
        
        // スラッグの生成
        $slug = $data['slug'] ?? SEOHelper::generateSlug($title);
        
        // スラッグの重複チェック
        $check = $this->db->fetch("SELECT id FROM posts WHERE slug = ?", [$slug]);
        if ($check) {
            $slug .= '-' . time();
        }

        $postData = [
            'title' => $title,
            'content' => $content,
            'excerpt' => $data['excerpt'] ?? SEOHelper::generateExcerpt($content),
            'slug' => $slug,
            'status' => $status,
            'author_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'published_at' => ($status === 'published') ? date('Y-m-d H:i:s') : null
        ];

        try {
            $postId = $this->postModel->create($postData);
            return [
                'status' => 'success',
                'message' => '投稿を作成しました',
                'data' => [
                    'id' => $postId,
                    'slug' => $slug,
                    'url' => url('?action=post&id=' . $postId)
                ]
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => '投稿の作成に失敗しました: ' . $e->getMessage(), 'code' => 500];
        }
    }
}
?>
