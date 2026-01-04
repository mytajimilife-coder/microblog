<?php
/**
 * 投稿管理ページ
 * 投稿の作成・編集・管理
 */

requirePermission('author');

$db = new Database();
$pdo = $db->connect();

$postModel = new Post($pdo);
$categoryModel = new Category($pdo);
$tagModel = new Tag($pdo);

// アクション判定
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? $_GET['edit'] ?? null;

// CSRFトークン生成
$csrfToken = SecurityHelper::generateCSRFToken();

switch ($action) {
    case 'list':
        include 'list.php';
        break;
    case 'create':
        include 'create.php';
        break;
    case 'edit':
        if ($id) {
            include 'edit.php';
        } else {
            header('Location: index.php?action=posts');
        }
        break;
    case 'delete':
        if ($id && $_POST) {
            include 'delete.php';
        } else {
            header('Location: index.php?action=posts');
        }
        break;
    case 'save':
        include 'save.php';
        break;
    default:
        include 'list.php';
        break;
}
?>