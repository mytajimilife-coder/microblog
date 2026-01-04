<?php
/**
 * 管理者コントロールパネル
 * 管理界面入口
 */

session_start();

// 設定読み込み
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';
require_once '../includes/model.php';
require_once '../includes/admin/auth.php';

// 認証チェック
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// データベース初期化
$db = new Database();
$pdo = $db->connect();

// 管理ダッシュボード
$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'dashboard':
        include 'dashboard.php';
        break;
    case 'posts':
        include 'posts/index.php';
        break;
    case 'categories':
        include 'categories/index.php';
        break;
    case 'tags':
        include 'tags/index.php';
        break;
    case 'settings':
        include 'settings.php';
        break;
    case 'firewall':
        include 'firewall.php';
        break;
    case 'contacts':
        include 'contacts.php';
        break;
    case 'users':
        include 'users/index.php';
        break;
    case 'themes':
        include 'themes.php';
        break;
    case 'logout':
        logout();
        break;
    default:
        include 'dashboard.php';
        break;
}
?>