<?php
/**
 * お問い合わせメッセージ管理
 */

session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';
require_once '../includes/contact_system.php';
require_once '../includes/admin/auth.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$db = new Database();
$pdo = $db->connect();
$contactSystem = new ContactSystem($pdo);
$message = '';

// 既読処理
if (isset($_GET['read'])) {
    $contactSystem->markAsRead($_GET['read']);
}

// 削除処理
if (isset($_GET['delete'])) {
    $db->query("DELETE FROM contact_messages WHERE id = ?", [$_GET['delete']]);
    $message = 'メッセージを削除しました。';
}

$messages = $contactSystem->getMessages();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>メッセージ管理 - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 1000px; margin: 0 auto; }
        .msg-item { border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; position: relative; }
        .unread { border-left: 5px solid #3498db; background: #f0f8ff; }
        .msg-meta { font-size: 13px; color: #7f8c8d; margin-bottom: 10px; }
        .msg-body { background: #fff; padding: 15px; border-radius: 4px; border: 1px inset #eee; }
        .actions { margin-top: 15px; }
        .btn-sm { font-size: 12px; margin-right: 10px; text-decoration: none; color: #3498db; }
    </style>
</head>
<body>
    <div class="card">
        <h1>📧 お問い合わせメッセージ</h1>
        <?php if ($message): ?><p><?php echo $message; ?></p><?php endif; ?>

        <?php foreach ($messages as $msg): ?>
            <div class="msg-item <?php echo !$msg['is_read'] ? 'unread' : ''; ?>">
                <div class="msg-meta">
                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong> (<?php echo htmlspecialchars($msg['email']); ?>) | 
                    送信日: <?php echo $msg['created_at']; ?> |
                    IP: <?php echo $msg['ip_address']; ?>
                </div>
                <h3>件名: <?php echo htmlspecialchars($msg['subject']); ?></h3>
                <div class="msg-body">
                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                </div>
                <div class="actions">
                    <?php if (!$msg['is_read']): ?>
                        <a href="?read=<?php echo $msg['id']; ?>" class="btn-sm">✅ 既読にする</a>
                    <?php endif; ?>
                    <a href="?delete=<?php echo $msg['id']; ?>" class="btn-sm" style="color:red;" onclick="return confirm('削除しますか？')">🗑️ 削除</a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($messages)): ?>
            <p>メッセージはありません。</p>
        <?php endif; ?>
        
        <p><a href="index.php">← ダッシュボードに戻る</a></p>
    </div>
</body>
</html>
