<?php
/**
 * 管理者ダッシュボード
 * サイトの概要と操作
 */

requireLogin();

// データベース初期化
$db = new Database();
$pdo = $db->connect();

// モデル初期化
$postModel = new Post($pdo);
$categoryModel = new Category($pdo);
$userModel = new User($pdo);

// 統計データ取得
$totalPosts = $postModel->count();
$publishedPosts = $postModel->count(['status' => 'published']);
$draftPosts = $postModel->count(['status' => 'draft']);
$totalCategories = $categoryModel->count();
$totalUsers = $userModel->count();

// 追加機能の通知取得
require_once '../includes/admin_notifications.php';
$notifications = new AdminNotifications($pdo);
$globalCount = $notifications->getGlobalCount();
$alertList = $notifications->getNotificationList();

// 最近の投稿取得
$recentPosts = $postModel->all('created_at DESC', 5);

// 現在のユーザー情報
$currentUser = getCurrentUser();

// メッセージ取得
$messageData = getAdminMessage();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード - <?php echo h(SITE_NAME); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: <?php echo JAPANESE_FONT_FAMILY; ?>;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
        }
        
        .sidebar h1 {
            font-size: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            display: block;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background-color: #34495e;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h2 {
            color: #2c3e50;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .content-section {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .recent-posts {
            list-style: none;
        }
        
        .post-item {
            padding: 15px 0;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .post-item:last-child {
            border-bottom: none;
        }
        
        .post-title {
            font-weight: 500;
            color: #2c3e50;
            text-decoration: none;
        }
        
        .post-title:hover {
            color: #3498db;
        }
        
        .post-meta {
            font-size: 12px;
            color: #666;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
    /* 追加スタイル */
    .alert-card { padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; border: 1px solid #ddd; }
    .alert-card.high { border-left: 5px solid #e74c3c; background: #fff5f5; }
    .alert-card.medium { border-left: 5px solid #f39c12; background: #fff9f0; }
    .alert-card.low { border-left: 5px solid #3498db; background: #f0f7ff; }
    .nav-badge { background: #e74c3c; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 5px; }
</style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h1>Microblog Admin</h1>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="index.php" class="nav-link active">🏠 ダッシュボード</a></li>
                    <li class="nav-item"><a href="index.php?action=posts" class="nav-link">📝 投稿管理</a></li>
                    <li class="nav-item"><a href="index.php?action=categories" class="nav-link">📁 カテゴリー</a></li>
                    <li class="nav-item"><a href="index.php?action=settings" class="nav-link">⚙️ 総合設定</a></li>
                    <li class="nav-item"><a href="index.php?action=themes" class="nav-link">🎨 テーマ管理</a></li>
                    <li class="nav-item"><a href="index.php?action=firewall" class="nav-link">🛡️ セキュリティ</a></li>
                    <li class="nav-item">
                        <a href="index.php?action=contacts" class="nav-link">
                            📧 お問い合わせ 
                            <?php if ($globalCount > 0): ?><span class="nav-badge"><?php echo $globalCount; ?></span><?php endif; ?>
                        </a>
                    </li>
                    <hr style="border: 0; border-top: 1px solid #34495e; margin: 10px 0;">
                    <li class="nav-item"><a href="index.php?action=logout" class="nav-link">🚪 ログアウト</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h2>管理者ダッシュボード <span style="font-size: 0.5em; color: #95a5a6; font-weight: normal;"><?php echo SITE_VERSION; ?></span></h2>
                <a href="../" target="_blank" class="btn">🚀 サイトを確認</a>
            </div>

            <!-- 通知センター (初心者向け) -->
            <?php if (!empty($alertList)): ?>
            <div class="content-section">
                <div class="section-title">📢 未処理の重要事項</div>
                <?php foreach ($alertList as $alert): ?>
                <div class="alert-card <?php echo $alert['priority']; ?>">
                    <span style="font-size: 24px;"><?php echo $alert['icon']; ?></span>
                    <div style="flex:1;">
                        <strong><?php echo $alert['title']; ?></strong>
                        <p style="font-size: 13px; color: #666;"><?php echo $alert['text']; ?></p>
                    </div>
                    <a href="index.php?action=<?php echo str_replace('.php', '', $alert['url']); ?>" class="btn">処理する</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title">総投稿数</div>
                    <div class="stat-number"><?php echo $totalPosts; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">カテゴリー</div>
                    <div class="stat-number"><?php echo $totalCategories; ?></div>
                </div>
                <!-- 閲覧数は統計機能から取得可能 -->
            </div>
            
            <div class="content-section">
                <div class="section-title">最近の投稿</div>
                <ul class="recent-posts">
                    <?php foreach ($recentPosts as $post): ?>
                    <li class="post-item">
                        <div>
                            <a href="index.php?action=posts&edit=<?php echo $post['id']; ?>" class="post-title">
                                <?php echo h($post['title']); ?>
                            </a>
                            <div class="post-meta">
                                <?php echo date('Y/m/d H:i', strtotime($post['created_at'])); ?> | <?php echo h($post['status']); ?>
                            </div>
                        </div>
                        <a href="index.php?action=posts&edit=<?php echo $post['id']; ?>" class="btn">編集</a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div style="margin-top: 20px;">
                    <a href="index.php?action=posts&new=1" class="btn" style="background: #27ae60;">➕ 新規投稿を作成</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>