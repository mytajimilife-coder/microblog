<?php
/**
 * タグコントローラー
 * タグ別投稿表示処理
 */

class TagController {
    private $db;
    private $postModel;
    private $tagModel;
    private $settingModel;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->postModel = new Post($pdo);
        $this->tagModel = new Tag($pdo);
        $this->settingModel = new Setting($pdo);
    }
    
    /**
     * タグ別投稿表示
     */
    public function view($tagSlug, $page = 1) {
        try {
            // タグ情報取得
            $tag = $this->tagModel->findBy('slug', $tagSlug);
            
            if (!$tag) {
                $this->renderError('タグが見つかりません');
                return;
            }
            
            // タグ別投稿取得
            $postsData = $this->postModel->getByTag($tagSlug, $page);
            
            // 関連タグ取得
            $relatedTags = $this->getRelatedTags($tag['id']);
            
            // 設定取得
            $settings = $this->settingModel->getMultiple([
                'site_name', 'site_description'
            ]);
            
            $siteInfo = [
                'name' => $settings['site_name'] ?? SITE_NAME,
                'description' => $settings['site_description'] ?? SITE_DESCRIPTION
            ];
            
            // SEO情報
            $seoTitle = $tag['name'] . ' | ' . $siteInfo['name'];
            $seoDescription = $tag['name'] . 'に関する投稿一覧';
            
            // ページネーション生成
            $pagination = $this->generatePagination($postsData, 'tag/' . $tagSlug);
            
            // ビュー出力
            $this->render('tag', [
                'tag' => $tag,
                'relatedTags' => $relatedTags,
                'posts' => $postsData['data'],
                'pagination' => $pagination,
                'siteInfo' => $siteInfo,
                'seoTitle' => $seoTitle,
                'seoDescription' => $seoDescription,
                'currentPage' => $page,
                'title' => $tag['name']
            ]);
            
        } catch (Exception $e) {
            $this->renderError('タグの読み込みに失敗しました: ' . $e->getMessage());
        }
    }
    
    /**
     * 関連タグ取得
     * 同じ投稿に含まれているタグを取得
     */
    private function getRelatedTags($tagId, $limit = 10) {
        $sql = "SELECT DISTINCT t.*, COUNT(pt2.post_id) as post_count
                FROM tags t
                INNER JOIN post_tags pt1 ON t.id = pt1.tag_id
                INNER JOIN post_tags pt2 ON pt1.post_id = pt2.post_id
                WHERE pt2.tag_id = ? AND t.id != ?
                GROUP BY t.id
                ORDER BY post_count DESC, t.name
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$tagId, $tagId, $limit]);
    }
    
    /**
     * ページネーション生成
     */
    private function generatePagination($postsData, $baseUrl) {
        if ($postsData['total_pages'] <= 1) {
            return '';
        }
        
        $html = '<nav class="pagination" aria-label="ページネーション">';
        $html .= '<ul class="pagination-list">';
        
        // 前へ
        if ($postsData['has_prev']) {
            $html .= '<li class="pagination-item">';
            $html .= '<a href="' . htmlspecialchars(URLHelper::build($baseUrl, ['page' => $postsData['current_page'] - 1])) . '" class="pagination-link">前へ</a>';
            $html .= '</li>';
        }
        
        // ページ番号
        $start = max(1, $postsData['current_page'] - 2);
        $end = min($postsData['total_pages'], $postsData['current_page'] + 2);
        
        if ($start > 1) {
            $html .= '<li class="pagination-item">';
            $html .= '<a href="' . htmlspecialchars(URLHelper::build($baseUrl, ['page' => 1])) . '" class="pagination-link">1</a>';
            $html .= '</li>';
            
            if ($start > 2) {
                $html .= '<li class="pagination-ellipsis"><span>...</span></li>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $activeClass = ($i == $postsData['current_page']) ? ' pagination-link-active' : '';
            $html .= '<li class="pagination-item' . (($i == $postsData['current_page']) ? ' active' : '') . '">';
            $html .= '<a href="' . htmlspecialchars(URLHelper::build($baseUrl, ['page' => $i])) . '" class="pagination-link' . $activeClass . '">' . $i . '</a>';
            $html .= '</li>';
        }
        
        if ($end < $postsData['total_pages']) {
            if ($end < $postsData['total_pages'] - 1) {
                $html .= '<li class="pagination-ellipsis"><span>...</span></li>';
            }
            
            $html .= '<li class="pagination-item">';
            $html .= '<a href="' . htmlspecialchars(URLHelper::build($baseUrl, ['page' => $postsData['total_pages']])) . '" class="pagination-link">' . $postsData['total_pages'] . '</a>';
            $html .= '</li>';
        }
        
        // 次へ
        if ($postsData['has_next']) {
            $html .= '<li class="pagination-item">';
            $html .= '<a href="' . htmlspecialchars(URLHelper::build($baseUrl, ['page' => $postsData['current_page'] + 1])) . '" class="pagination-link">次へ</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
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