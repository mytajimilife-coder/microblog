<?php
/**
 * ホームコントローラー
 * ブログ首页處理
 */

class HomeController {
    private $db;
    private $postModel;
    private $categoryModel;
    private $tagModel;
    private $settingModel;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->postModel = new Post($pdo);
        $this->categoryModel = new Category($pdo);
        $this->tagModel = new Tag($pdo);
        $this->settingModel = new Setting($pdo);
    }
    
    /**
     * ホームページ表示
     */
    public function index($page = 1) {
        try {
            // 設定取得
            $settings = $this->settingModel->getMultiple([
                'site_name', 'site_description', 'posts_per_page'
            ]);
            
            $postsPerPage = (int)($settings['posts_per_page'] ?? POSTS_PER_PAGE);
            
            // 投稿取得
            $postsData = $this->postModel->getPublished($page, $postsPerPage);
            
            // 人気タグ取得
            $popularTags = $this->tagModel->getPopular(15);
            
            // カテゴリー取得
            $categories = $this->categoryModel->getHierarchical();
            
            // サイト情報
            $siteInfo = [
                'name' => $settings['site_name'] ?? SITE_NAME,
                'description' => $settings['site_description'] ?? SITE_DESCRIPTION
            ];
            
            // ビュー出力
            $this->render('home', [
                'posts' => $postsData['data'],
                'pagination' => $this->generatePagination($postsData, ''),
                'popularTags' => $popularTags,
                'categories' => $categories,
                'siteInfo' => $siteInfo,
                'currentPage' => $page,
                'title' => $siteInfo['name']
            ]);
            
        } catch (Exception $e) {
            $this->renderError('ホームページの読み込みに失敗しました: ' . $e->getMessage());
        }
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
        // データをスコープに展開
        extract($data);
        
        // ヘッダー出力
        include 'themes/default/header.php';
        
        // テンプレート出力
        include "themes/default/{$template}.php";
        
        // フッター出力
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