<?php
/**
 * サイトマップ生成
 */

session_start();
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';
require_once 'includes/model.php';

// データベース接続
$db = new Database();
$pdo = $db->connect();

// モデル初期化
$postModel = new Post($pdo);
$categoryModel = new Category($pdo);
$tagModel = new Tag($pdo);

// データ取得
$posts = $postModel->where(['status' => 'published'], [], 'updated_at DESC', 1000);
$categories = $categoryModel->all();
$tags = $tagModel->all();

// Content-Type設定
header('Content-Type: application/xml; charset=utf-8');

// XML出力
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- ホームページ -->
    <url>
        <loc><?php echo htmlspecialchars(BASE_URL, ENT_XML1); ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- 記事 -->
    <?php foreach ($posts as $post): ?>
    <url>
        <loc><?php echo htmlspecialchars(url('?action=post&id=' . $post['id']), ENT_XML1); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($post['updated_at'])); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- カテゴリー -->
    <?php foreach ($categories as $category): ?>
    <url>
        <loc><?php echo htmlspecialchars(url('?action=category&category=' . $category['slug']), ENT_XML1); ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- タグ -->
    <?php foreach ($tags as $tag): ?>
    <url>
        <loc><?php echo htmlspecialchars(url('?action=tag&tag=' . $tag['slug']), ENT_XML1); ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
    <?php endforeach; ?>
</urlset>
