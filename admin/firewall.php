<?php
/**
 * セキュリティ・ファイヤーウォール管理
 */

session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';
require_once '../includes/security/ip_manager.php';
require_once '../includes/admin/auth.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$db = new Database();
$pdo = $db->connect();
$ipManager = new IPManager($pdo);
$message = '';

// IPブロック解除
if (isset($_GET['unblock'])) {
    $db->query("DELETE FROM ip_bans WHERE ip_address = ?", [$_GET['unblock']]);
    $message = 'IPアドレス ' . htmlspecialchars($_GET['unblock']) . ' の制限を解除しました。';
}

// IP手動ブロック
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_ip'])) {
    $ipManager->block($_POST['block_ip'], $_POST['reason'] ?? '手動ブロック', $_POST['days'] ?: null);
    $message = 'IPアドレス ' . htmlspecialchars($_POST['block_ip']) . ' をブロックしました。';
}

// ブロックリスト取得
$blockedList = $db->fetchAll("SELECT * FROM ip_bans ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>セキュリティ管理 - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 1000px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; }
        .badge { background: #e74c3c; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .form-inline { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-red { background: #e74c3c; color: white; }
        .btn-blue { background: #3498db; color: white; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🛡️ セキュリティ・IPブロック管理</h1>
        <?php if ($message): ?><p style="color: blue;"><?php echo $message; ?></p><?php endif; ?>

        <div class="form-inline">
            <h3>新規IPブロック</h3>
            <form method="POST">
                <input type="text" name="block_ip" placeholder="IPアドレス (例: 123.123.123.123)" required>
                <input type="text" name="reason" placeholder="ブロック理由">
                <input type="number" name="days" placeholder="期間(日) ※空なら無期限">
                <button type="submit" class="btn btn-red">ブロック実行</button>
            </form>
        </div>

        <h3>ブロック中のIPアドレス一覧</h3>
        <table>
            <thead>
                <tr>
                    <th>IPアドレス</th>
                    <th>理由</th>
                    <th>ブロック日時</th>
                    <th>期限</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blockedList as $ip): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($ip['ip_address']); ?></strong></td>
                    <td><?php echo htmlspecialchars($ip['reason']); ?></td>
                    <td><?php echo $ip['created_at']; ?></td>
                    <td><?php echo $ip['expires_at'] ?: '<span style="color:red">無期限</span>'; ?></td>
                    <td>
                        <a href="?unblock=<?php echo urlencode($ip['ip_address']); ?>" onclick="return confirm('解除しますか？')" style="color:#3498db;">解除</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($blockedList)): ?>
                <tr><td colspan="5" style="text-align:center;">現在ブロック中のIPはありません。</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p style="margin-top:20px;"><a href="index.php">← ダッシュボードに戻る</a></p>
    </div>
</body>
</html>
