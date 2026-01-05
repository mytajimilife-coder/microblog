<?php
/**
 * 投稿コントローラー
 * 個別投稿表示処理
 */

class PostController {
    private $db;
    private $postModel;
    private $categoryModel;
    private $tagModel;
    private $settingModel;
    private $commentModel;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->postModel = new Post($pdo);
        $this->categoryModel = new Category($pdo);
        $this->tagModel = new Tag($pdo);
        $this->settingModel = new Setting($pdo);
        $this->commentModel = new Comment($pdo);
    }
    
    /**
     * 個別投稿表示
     */
    public function view($id) {
        try {
            // パラメータ判定（IDまたはスラッグ）
            if (is_numeric($id)) {
                $post = $this->postModel->find($id);
            } else {
                $post = $this->postModel->findBySlug($id);
            }
            
            if (!$post || $post['status'] !== 'published') {
                $this->renderError('投稿が見つかりません');
                return;
            }
            
            // ビュー数増加
            $this->postModel->incrementViewCount($post['id']);
            $post['view_count']++;
            
            // 関連投稿取得
            $relatedPosts = $this->postModel->getRelated($post['id'], 3);
            
            // 投稿のカテゴリー取得
            $categories = $this->getPostCategories($post['id']);
            
            // 投稿のタグ取得
            $tags = $this->getPostTags($post['id']);
            
            // コメント取得
            $comments = $this->commentModel->getByPost($post['id']);
            $commentCount = $this->commentModel->countByPost($post['id']);
            
            // 設定取得
            $settings = $this->settingModel->getMultiple([
                'site_name', 'site_description'
            ]);
            
            $siteInfo = [
                'name' => $settings['site_name'] ?? SITE_NAME,
                'description' => $settings['site_description'] ?? SITE_DESCRIPTION
            ];
            
            // SEO情報
            $seoTitle = $post['title'] . ' | ' . $siteInfo['name'];
            $seoDescription = HTMLHelper::excerpt($post['content'], 160);
            $seoImage = $post['featured_image'] ? url($post['featured_image']) : null;
            
            // パンくずリスト
            require_once 'includes/breadcrumb.php';
            $breadcrumb = new Breadcrumb();
            $breadcrumb->fromPost($post, $categories);
            
            // ビュー出力
            $this->render('post', [
                'post' => $post,
                'categories' => $categories,
                'tags' => $tags,
                'comments' => $comments,
                'commentCount' => $commentCount,
                'relatedPosts' => $relatedPosts,
                'siteInfo' => $siteInfo,
                'seoTitle' => $seoTitle,
                'seoDescription' => $seoDescription,
                'seoImage' => $seoImage,
                'title' => $post['title'],
                'breadcrumb' => $breadcrumb->render()
            ]);
            
        } catch (Exception $e) {
            $this->renderError('投稿の読み込みに失敗しました: ' . $e->getMessage());
        }
    }
    
    /**
     * 投稿のカテゴリー取得
     */
    private function getPostCategories($postId) {
        $sql = "SELECT c.* 
                FROM categories c
                INNER JOIN post_categories pc ON c.id = pc.category_id
                WHERE pc.post_id = ?
                ORDER BY c.name";
        
        return $this->db->fetchAll($sql, [$postId]);
    }
    
    /**
     * 投稿のタグ取得
     */
    private function getPostTags($postId) {
        $sql = "SELECT t.* 
                FROM tags t
                INNER JOIN post_tags pt ON t.id = pt.tag_id
                WHERE pt.post_id = ?
                ORDER BY t.name";
        
        return $this->db->fetchAll($sql, [$postId]);
    }
    
    /**
     * ビュー描画
     */
    private function render($template, $data = []) {
        extract($data);
        include 'themes/default/header.php';
        include "themes/default/{$template}.php";
        include 'themes/default/footer.php';
    }
    
    /**
     * エラー画面描画
     */
    private function renderError($message) {
        $siteInfo = [
            'name' => SITE_NAME,
            'description' => SITE_DESCRIPTION
        ];
        
        extract(['message' => $message, 'siteInfo' => $siteInfo]);
        include 'themes/default/error.php';
    }
}
?>