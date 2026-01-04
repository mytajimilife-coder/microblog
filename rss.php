<?php
/**
 * RSSフィード生成
 */

session_start();
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';
require_once 'includes/model.php';

// データベース接続
$db = new Database();
$pdo = $db->connect();

// 設定取得
$settingModel = new Setting($pdo);
$siteName = $settingModel->get('site_name', SITE_NAME);
$siteDescription = $settingModel->get('site_description', SITE_DESCRIPTION);

// 最新記事取得
$postModel = new Post($pdo);
$posts = $postModel->getPublished(1, 20);

// Content-Type設定
header('Content-Type: application/rss+xml; charset=utf-8');

// RSS出力
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title><?php echo htmlspecialchars($siteName, ENT_XML1); ?></title>
        <link><?php echo htmlspecialchars(BASE_URL, ENT_XML1); ?></link>
        <description><?php echo htmlspecialchars($siteDescription, ENT_XML1); ?></description>
        <language>ja</language>
        <lastBuildDate><?php echo date('r'); ?></lastBuildDate>
        <atom:link href="<?php echo htmlspecialchars(BASE_URL . 'rss.php', ENT_XML1); ?>" rel="self" type="application/rss+xml" />
        
        <?php foreach ($posts['data'] as $post): ?>
        <item>
            <title><?php echo htmlspecialchars($post['title'], ENT_XML1); ?></title>
            <link><?php echo htmlspecialchars(url('?action=post&id=' . $post['id']), ENT_XML1); ?></link>
            <description><?php echo htmlspecialchars(strip_tags($post['excerpt'] ?: HTMLHelper::excerpt($post['content'], 200)), ENT_XML1); ?></description>
            <pubDate><?php echo date('r', strtotime($post['published_at'] ?: $post['created_at'])); ?></pubDate>
            <guid><?php echo htmlspecialchars(url('?action=post&id=' . $post['id']), ENT_XML1); ?></guid>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>
