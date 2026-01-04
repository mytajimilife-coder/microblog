<?php
/**
 * Microblog 設定ファイル
 * 基本設定とデータベース設定
 */

// エラー表示設定（開発時のみ有効）
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// サイトの基本設定
define('SITE_NAME', 'Microblog');
define('SITE_DESCRIPTION', '日本語専用ブログシステム');
define('SITE_VERSION', 'Beta v1.0.0');
define('BASE_URL', (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']));

// ページ設定
define('POSTS_PER_PAGE', 10);
define('ADMIN_POSTS_PER_PAGE', 20);

// テーマ設定 (デフォルト候補)
define('FALLBACK_THEME', 'sleek');

// アップロード設定
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// セキュリティ設定
define('SESSION_TIMEOUT', 3600); // 1時間
define('CSRF_TOKEN_NAME', 'microblog_csrf_token');

// テーマ設定
define('DEFAULT_THEME', 'default');

// 言語設定
define('DEFAULT_LANGUAGE', 'ja');
define('TIMEZONE', 'Asia/Tokyo');
date_default_timezone_set(TIMEZONE);

// 日本語フォント設定
define('JAPANESE_FONT_FAMILY', '"Hiragino Sans", "Yu Gothic", "Meiryo", sans-serif');

// データベース設定（インストール時に更新）
if (file_exists(__DIR__ . '/database.php')) {
    require_once __DIR__ . '/database.php';
} else {
    // デフォルト設定（インストール時に変更される）
    define('DB_TYPE', 'mysql'); // mysql または postgresql
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'microblog');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

// CSRFトークン生成
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// CSRFトークン検証
function validateCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// HTML特殊文字エスケープ
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// URL生成
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

// ファイルアップロードパス
function upload_path($filename = '') {
    return UPLOAD_PATH . $filename;
}

// 日本語テキスト処理
function truncate_japanese_text($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text, 'UTF-8') <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
}
?>