<?php
/**
 * カテゴリーコントローラー
 * カテゴリー別投稿表示処理
 */

class CategoryController {
    private $db;
    private $postModel;
    private $categoryModel;
    private $settingModel;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->postModel = new Post($pdo);
        $this->categoryModel = new Category($pdo);
        $this->settingModel = new Setting($pdo);
    }
    
    /**
     * カテゴリー別投稿表示
     */
    public function view($categorySlug, $page = 1) {
        try {
            // カテゴリー情報取得
            $category = $this->categoryModel->findBy('slug', $categorySlug);
            
            if (!$category) {
                $this->renderError('カテゴリーが見つかりません');
                return;
            }
            
            // カテゴリー別投稿取得
            $postsData = $this->postModel->getByCategory($categorySlug, $page);
            
            // サブカテゴリー取得
            $childCategories = $this->categoryModel->getChildren($category['id']);
            
            // カテゴリー階層パス取得
            $categoryPath = $this->categoryModel->getPath($category['id']);
            
            // 設定取得
            $settings = $this->settingModel->getMultiple([
                'site_name', 'site_description'
            ]);
            
            $siteInfo = [
                'name' => $settings['site_name'] ?? SITE_NAME,
                'description' => $settings['site_description'] ?? SITE_DESCRIPTION
            ];
            
            // SEO情報
            $seoTitle = $category['name'] . ' | ' . $siteInfo['name'];
            $seoDescription = $category['description'] ?: $category['name'] . 'に関する投稿一覧';
            
            // ページネーション生成
            $pagination = $this->generatePagination($postsData, 'category/' . $categorySlug);
            
            // ビュー出力
            $this->render('category', [
                'category' => $category,
                'categoryPath' => $categoryPath,
                'childCategories' => $childCategories,
                'posts' => $postsData['data'],
                'pagination' => $pagination,
                'siteInfo' => $siteInfo,
                'seoTitle' => $seoTitle,
                'seoDescription' => $seoDescription,
                'currentPage' => $page,
                'title' => $category['name']
            ]);
            
        } catch (Exception $e) {
            $this->renderError('カテゴリーの読み込みに失敗しました: ' . $e->getMessage());
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