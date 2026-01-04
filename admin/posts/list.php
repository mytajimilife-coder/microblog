<?php
/**
 * 投稿一覧ページ
 */

// ページネーションパラメータ
$page = (int)($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// 検索条件設定
$conditions = [];
if ($status !== 'all') {
    $conditions['status'] = $status;
}

if (!empty($search)) {
    $sql = "SELECT * FROM posts WHERE title LIKE ? OR content LIKE ?";
    if (!empty($conditions)) {
        $where = [];
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = ?";
        }
        $sql .= " AND " . implode(' AND ', $where);
    }
    $sql .= " ORDER BY created_at DESC";
    
    $searchTerm = '%' . $search . '%';
    $params = array_merge([$searchTerm, $searchTerm], array_values($conditions));
    
    // 全件取得（簡易実装）
    $allPosts = $db->fetchAll($sql, $params);
    
    // ページネーション手動実装
    $postsPerPage = 20;
    $offset = ($page - 1) * $postsPerPage;
    $total = count($allPosts);
    $totalPages = ceil($total / $postsPerPage);
    
    $posts = array_slice($allPosts, $offset, $postsPerPage);
} else {
    // ページネーション対応取得
    $postsData = $postModel->paginate($page, 20, $conditions, 'created_at DESC');
    $posts = $postsData['data'];
    $totalPages = $postsData['total_pages'];
    $currentPage = $postsData['current_page'];
    $total = $postsData['total'];
}

// カテゴリー・タグ統計（サイドバー用）
$categoryStats = $db->fetchAll("SELECT c.name, c.slug, COUNT(pc.post_id) as post_count 
                               FROM categories c 
                               LEFT JOIN post_categories pc ON c.id = pc.category_id 
                               GROUP BY c.id 
                               ORDER BY post_count DESC 
                               LIMIT 10");

$tagStats = $db->fetchAll("SELECT t.name, t.slug, COUNT(pt.post_id) as post_count 
                          FROM tags t 
                          LEFT JOIN post_tags pt ON t.id = pt.tag_id 
                          GROUP BY t.id 
                          ORDER BY post_count DESC 
                          LIMIT 15");

// メッセージ取得
$messageData = getAdminMessage();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿管理 - <?php echo h(SITE_NAME); ?></title>
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
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #219a52;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
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
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
        }
        
        .main-section {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .sidebar-section {
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
        
        .filter-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-input, .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }
        
        .posts-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .posts-table th,
        .posts-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .posts-table th {
            background-color: #f8f9fa;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .status-published {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-private {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 2px;
            background: white;
            border: 1px solid #ddd;
            color: #333;
            text-decoration: none;
            border-radius: 3px;
        }
        
        .pagination a:hover,
        .pagination a.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .stats-list {
            list-style: none;
        }
        
        .stats-item {
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats-item:last-child {
            border-bottom: none;
        }
        
        .stats-link {
            color: #3498db;
            text-decoration: none;
        }
        
        .stats-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h1>管理パネル</h1>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="../dashboard.php" class="nav-link">ダッシュボード</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?action=posts" class="nav-link active">投稿管理</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=categories" class="nav-link">カテゴリー</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=tags" class="nav-link">タグ</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=users" class="nav-link">ユーザー</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=settings" class="nav-link">設定</a>
                    </li>
                    <li class="nav-item">
                        <a href="../../" class="nav-link" target="_blank">サイトを見る</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=logout" class="nav-link">ログアウト</a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h2>投稿管理</h2>
                <div class="user-info">
                    <a href="index.php?action=create" class="btn btn-success">新規投稿</a>
                </div>
            </div>
            
            <?php if ($messageData['message']): ?>
                <div class="alert alert-<?php echo $messageData['type'] === 'error' ? 'error' : 'success'; ?>">
                    <?php echo h($messageData['message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="content-grid">
                <div class="main-section">
                    <div class="filter-bar">
                        <form method="get" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                            <input type="hidden" name="action" value="posts">
                            
                            <div class="filter-group">
                                <label>ステータス:</label>
                                <select name="status" class="filter-select">
                                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>すべて</option>
                                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>公開済み</option>
                                    <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>下書き</option>
                                    <option value="private" <?php echo $status === 'private' ? 'selected' : ''; ?>>非公開</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label>検索:</label>
                                <input type="text" name="search" value="<?php echo h($search); ?>" placeholder="タイトルまたは内容で検索" class="filter-input">
                            </div>
                            
                            <button type="submit" class="btn">検索</button>
                            
                            <?php if ($search || $status !== 'all'): ?>
                                <a href="index.php?action=posts" class="btn">クリア</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <table class="posts-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>タイトル</th>
                                <th>ステータス</th>
                                <th>作成日</th>
                                <th>更新日</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($posts)): ?>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td><?php echo $post['id']; ?></td>
                                        <td>
                                            <strong><?php echo h($post['title']); ?></strong>
                                            <?php if ($post['featured_image']): ?>
                                                <br><small>画像付き</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $post['status']; ?>">
                                                <?php 
                                                switch($post['status']) {
                                                    case 'published': echo '公開済み'; break;
                                                    case 'draft': echo '下書き'; break;
                                                    case 'private': echo '非公開'; break;
                                                    default: echo $post['status'];
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo DateTimeHelper::formatJapanese(strtotime($post['created_at'])); ?></td>
                                        <td><?php echo DateTimeHelper::formatJapanese(strtotime($post['updated_at'])); ?></td>
                                        <td>
                                            <a href="index.php?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-small">編集</a>
                                            <a href="../../?action=post&id=<?php echo $post['slug']; ?>" class="btn btn-small" target="_blank">表示</a>
                                            <form method="post" style="display: inline;" onsubmit="return confirm('この投稿を削除しますか？');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <button type="submit" class="btn btn-danger btn-small">削除</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                                        投稿が見つかりません
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="index.php?action=posts&page=<?php echo $i; ?>&status=<?php echo h($status); ?>&search=<?php echo h($search); ?>" 
                                   class="<?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-section">
                    <div class="section-title">カテゴリー別統計</div>
                    <ul class="stats-list">
                        <?php foreach ($categoryStats as $category): ?>
                            <li class="stats-item">
                                <a href="../categories/index.php?slug=<?php echo $category['slug']; ?>" class="stats-link">
                                    <?php echo h($category['name']); ?>
                                </a>
                                <span>(<?php echo $category['post_count']; ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="sidebar-section">
                    <div class="section-title">人気のタグ</div>
                    <ul class="stats-list">
                        <?php foreach ($tagStats as $tag): ?>
                            <li class="stats-item">
                                <a href="../tags/index.php?slug=<?php echo $tag['slug']; ?>" class="stats-link">
                                    <?php echo h($tag['name']); ?>
                                </a>
                                <span>(<?php echo $tag['post_count']; ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>