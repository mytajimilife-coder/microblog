<?php
/**
 * コメント管理
 */
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/model.php';
require_once __DIR__ . '/../includes/models/comment.php';
require_once __DIR__ . '/../includes/admin/auth.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$db = new Database();
$pdo = $db->connect();
$commentModel = new Comment($pdo);
$message = '';

// アクション処理
if (isset($_GET['approve'])) {
    $commentModel->approve($_GET['approve']);
    $message = 'コメントを承認しました。';
}

if (isset($_GET['spam'])) {
    $commentModel->markAsSpam($_GET['spam']);
    $message = 'コメントをスパムとしてマークしました。';
}

if (isset($_GET['delete'])) {
    $commentModel->delete($_GET['delete']);
    $message = 'コメントを削除しました。';
}

// 全コメント取得
$sql = "SELECT c.*, p.title as post_title 
        FROM comments c 
        LEFT JOIN posts p ON c.post_id = p.id 
        ORDER BY c.created_at DESC";
$comments = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>コメント管理 - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 1100px; margin: 0 auto; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .admin-table th, .admin-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f9fa; font-weight: bold; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-spam { background: #f8d7da; color: #721c24; }
        .btn-action { text-decoration: none; font-size: 13px; margin-right: 8px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .comment-text { font-size: 14px; color: #444; max-width: 400px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>💬 コメント管理</h1>
        
        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>投稿</th>
                    <th>投稿者</th>
                    <th>内容</th>
                    <th>日付</th>
                    <th>ステータス</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $c): ?>
                    <tr>
                        <td><small><?php echo h($c['post_title']); ?></small></td>
                        <td>
                            <strong><?php echo h($c['author_name']); ?></strong><br>
                            <small><?php echo h($c['author_email']); ?></small>
                        </td>
                        <td>
                            <div class="comment-text"><?php echo nl2br(h(JapaneseTextProcessor::truncate($c['content'], 100))); ?></div>
                        </td>
                        <td><small><?php echo date('Y/m/d H:i', strtotime($c['created_at'])); ?></small></td>
                        <td>
                            <span class="status-badge status-<?php echo $c['status']; ?>">
                                <?php echo $c['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($c['status'] === 'pending'): ?>
                                <a href="?approve=<?php echo $c['id']; ?>" class="btn-action" style="color:green;">承認</a>
                            <?php endif; ?>
                            <?php if ($c['status'] !== 'spam'): ?>
                                <a href="?spam=<?php echo $c['id']; ?>" class="btn-action" style="color:orange;">スパム</a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $c['id']; ?>" class="btn-action" style="color:red;" onclick="return confirm('本当に削除しますか？')">削除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($comments)): ?>
            <p style="margin-top: 20px; text-align: center; color: #666;">コメントはまだありません。</p>
        <?php endif; ?>
        
        <p style="margin-top: 30px;"><a href="index.php" style="text-decoration: none; color: #3498db;">← ダッシュボードに戻る</a></p>
    </div>
</body>
</html>
