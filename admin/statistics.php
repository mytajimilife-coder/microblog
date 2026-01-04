<?php
/**
 * 統計ダッシュボード
 */

session_start();

require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';
require_once '../includes/model.php';
require_once '../includes/admin/auth.php';
require_once '../includes/statistics.php';
require_once '../includes/scheduled_posts.php';

// ログインチェック
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// データベース接続
$db = new Database();
$pdo = $db->connect();

$stats = new Statistics($pdo);
$scheduler = new ScheduledPosts($pdo);

// 統計データ取得
$summary = $stats->getSummary();
$popularPosts = $stats->getPopularPosts(10);
$trendingPosts = $stats->getTrendingPosts(5);
$categoryStats = $stats->getCategoryStats();
$monthlyStats = $stats->getMonthlyStats(6);
$scheduledStats = $scheduler->getStats();
$nextScheduled = $scheduler->getNextScheduled();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>統計ダッシュボード - <?php echo h(SITE_NAME); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: <?php echo JAPANESE_FONT_FAMILY; ?>;
            background: #f5f7fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .back-link {
            color: #3498db;
            text-decoration: none;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-card.primary { border-left: 4px solid #3498db; }
        .stat-card.success { border-left: 4px solid #27ae60; }
        .stat-card.warning { border-left: 4px solid #f39c12; }
        .stat-card.info { border-left: 4px solid #9b59b6; }
        
        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .chart-container {
            margin-top: 20px;
        }
        
        .scheduled-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #27ae60;
        }
        
        .scheduled-info strong {
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="index.php" class="back-link">← ダッシュボードに戻る</a>
            <h1>📊 統計ダッシュボード</h1>
        </div>
        
        <!-- サマリー統計 -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <h3>総投稿数</h3>
                <div class="stat-value"><?php echo number_format($summary['total_posts']); ?></div>
            </div>
            
            <div class="stat-card success">
                <h3>総閲覧数</h3>
                <div class="stat-value"><?php echo number_format($summary['total_views']); ?></div>
            </div>
            
            <div class="stat-card warning">
                <h3>総コメント数</h3>
                <div class="stat-value"><?php echo number_format($summary['total_comments']); ?></div>
            </div>
            
            <div class="stat-card info">
                <h3>今月の投稿</h3>
                <div class="stat-value"><?php echo number_format($summary['this_month_posts']); ?></div>
            </div>
        </div>
        
        <!-- 予約投稿情報 -->
        <?php if ($scheduledStats['total'] > 0): ?>
        <div class="section">
            <h2>📅 予約投稿</h2>
            <div class="scheduled-info">
                <p><strong>予約中:</strong> <?php echo $scheduledStats['upcoming']; ?>件</p>
                <?php if ($scheduledStats['overdue'] > 0): ?>
                    <p style="color: #e74c3c;"><strong>公開待ち:</strong> <?php echo $scheduledStats['overdue']; ?>件（要確認）</p>
                <?php endif; ?>
                
                <?php if ($nextScheduled): ?>
                    <p style="margin-top: 10px;">
                        <strong>次の予約:</strong> 
                        <?php echo h($nextScheduled['title']); ?> 
                        (<?php echo date('Y年m月d日 H:i', strtotime($nextScheduled['scheduled_publish_at'])); ?>)
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- トレンド記事 -->
        <div class="section">
            <h2>🔥 トレンド記事（過去7日間）</h2>
            <table>
                <thead>
                    <tr>
                        <th>タイトル</th>
                        <th>閲覧数</th>
                        <th>投稿日</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trendingPosts as $post): ?>
                    <tr>
                        <td><?php echo h($post['title']); ?></td>
                        <td><?php echo number_format($post['view_count']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- 人気記事 -->
        <div class="section">
            <h2>⭐ 人気記事（過去30日間）</h2>
            <table>
                <thead>
                    <tr>
                        <th>タイトル</th>
                        <th>閲覧数</th>
                        <th>投稿日</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($popularPosts as $post): ?>
                    <tr>
                        <td><?php echo h($post['title']); ?></td>
                        <td><?php echo number_format($post['view_count']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- カテゴリー統計 -->
        <div class="section">
            <h2>📁 カテゴリー別統計</h2>
            <table>
                <thead>
                    <tr>
                        <th>カテゴリー</th>
                        <th>投稿数</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categoryStats as $cat): ?>
                    <tr>
                        <td><?php echo h($cat['name']); ?></td>
                        <td><?php echo number_format($cat['post_count']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- 月別統計 -->
        <div class="section">
            <h2>📈 月別投稿統計</h2>
            <table>
                <thead>
                    <tr>
                        <th>月</th>
                        <th>投稿数</th>
                        <th>総閲覧数</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthlyStats as $month): ?>
                    <tr>
                        <td><?php echo h($month['month']); ?></td>
                        <td><?php echo number_format($month['post_count']); ?></td>
                        <td><?php echo number_format($month['total_views']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
