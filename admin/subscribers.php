<?php
/**
 * 購読者管理
 */
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/models/subscriber.php';
require_once __DIR__ . '/../includes/admin/auth.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$db = new Database();
$pdo = $db->connect();
$subscriberModel = new Subscriber($pdo);
$message = '';

// 削除処理
if (isset($_GET['delete'])) {
    $email = $_GET['delete'];
    $subscriberModel->unsubscribe($email);
    $message = '購読を解除しました。';
}

$subscribers = $subscriberModel->getAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>購読者管理 - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 1000px; margin: 0 auto; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .admin-table th, .admin-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f9fa; font-weight: bold; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .status-active { background: #d4edda; color: #155724; }
        .status-unsubscribed { background: #f8d7da; color: #721c24; }
        .btn-delete { color: #dc3545; text-decoration: none; font-size: 14px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="card">
        <h1>👥 ニュースレター購読者管理</h1>
        <p>合計: <?php echo count($subscribers); ?> 名の購読者</p>
        
        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>メールアドレス</th>
                    <th>登録日</th>
                    <th>IPアドレス</th>
                    <th>ステータス</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $s): ?>
                    <tr>
                        <td><?php echo h($s['email']); ?></td>
                        <td><?php echo date('Y/m/d H:i', strtotime($s['created_at'])); ?></td>
                        <td><?php echo h($s['ip_address']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $s['status']; ?>">
                                <?php echo $s['status'] === 'active' ? '有効' : '解除済み'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($s['status'] === 'active'): ?>
                                <a href="?delete=<?php echo urlencode($s['email']); ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('本当に解除しますか？')">🗑️ 解除</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($subscribers)): ?>
            <p style="margin-top: 20px; text-align: center; color: #666;">購読者はまだいません。</p>
        <?php endif; ?>
        
        <p style="margin-top: 30px;"><a href="index.php" style="text-decoration: none; color: #3498db;">← ダッシュボードに戻る</a></p>
    </div>
</body>
</html>
