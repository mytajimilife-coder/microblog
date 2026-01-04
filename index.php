<?php
/**
 * Microblog - 日本语支持のブログシステム
 * メインインデックスファイル
 */

session_start();

// コアファイルの読み込み
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';
require_once 'includes/model.php';
require_once 'includes/hooks.php';
require_once 'includes/site_settings.php';
require_once 'includes/cache.php';
require_once 'includes/security/ip_manager.php';

// データベース接続
$db = new Database();
$pdo = $db->connect();

// IPブロックチェック
$ipManager = new IPManager($pdo);
if ($ipManager->isBlocked()) {
    http_response_code(403);
    die('アクセス制限：お使いのIPアドレスはセキュリティ上の理由で制限されています。');
}

// サイト設定とメンテナンスModeチェック
$settings = new SiteSettings($pdo);
if ($settings->isMaintenanceMode()) {
    $settings->displayMaintenancePage();
}

// 現在のテーマをDBから取得（なければFALLBACK_THEME）
$activeTheme = $settings->get('theme', FALLBACK_THEME);
define('ACTIVE_THEME', $activeTheme);
define('THEME_PATH', BASE_URL . '/themes/' . $activeTheme);
define('THEME_DIR', __DIR__ . '/themes/' . $activeTheme);

// 初期化アクション
Hooks::doAction('init');

// URLパラメータの取得
$action = $_GET['action'] ?? 'home';
$page = $_GET['page'] ?? 1;
$category = $_GET['category'] ?? null;
$tag = $_GET['tag'] ?? null;
$id = $_GET['id'] ?? null;

// ページ読み込み
switch ($action) {
    case 'home':
        require_once 'includes/controllers/home_controller.php';
        $controller = new HomeController($pdo);
        $controller->index($page);
        break;
        
    case 'post':
        require_once 'includes/controllers/post_controller.php';
        $controller = new PostController($pdo);
        $controller->view($id);
        break;
        
    case 'category':
        require_once 'includes/controllers/category_controller.php';
        $controller = new CategoryController($pdo);
        $controller->view($category, $page);
        break;
        
    case 'tag':
        require_once 'includes/controllers/tag_controller.php';
        $controller = new TagController($pdo);
        $controller->view($tag, $page);
        break;
        
    case 'search':
        require_once 'includes/controllers/search_controller.php';
        $query = $_GET['q'] ?? '';
        $controller = new SearchController($pdo);
        $controller->search($query, $page);
        break;
        
    case 'admin':
        header('Location: admin/');
        break;
        
    default:
        http_response_code(404);
        echo "ページが見つかりません";
        break;
}
?>