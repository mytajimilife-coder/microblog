<?php
/**
 * バックアップ管理画面
 */

session_start();

require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';
require_once '../includes/model.php';
require_once '../includes/admin/auth.php';
require_once '../includes/backup.php';

// ログインチェック
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// データベース接続
$db = new Database();
$pdo = $db->connect();

$backup = new DatabaseBackup($pdo);
$message = '';
$error = '';

// アクション処理
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $result = $backup->create();
            if ($result['success']) {
                $message = 'バックアップを作成しました: ' . $result['filename'];
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'restore':
            $filename = $_POST['filename'] ?? '';
            if ($filename) {
                $result = $backup->restore($filename);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
            break;
            
        case 'delete':
            $filename = $_POST['filename'] ?? '';
            if ($filename) {
                $result = $backup->delete($filename);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
            break;
    }
}

// バックアップ一覧取得
$backups = $backup->list();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>バックアップ管理 - <?php echo h(SITE_NAME); ?></title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .create-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← ダッシュボードに戻る</a>
        
        <h1>バックアップ管理</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo h($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo h($error); ?></div>
        <?php endif; ?>
        
        <div class="create-section">
            <h2>新規バックアップ作成</h2>
            <p>データベースの完全バックアップを作成します。</p>
            <form method="post" style="margin-top: 15px;">
                <input type="hidden" name="action" value="create">
                <button type="submit" class="btn btn-success">バックアップを作成</button>
            </form>
        </div>
        
        <h2>バックアップ一覧</h2>
        
        <?php if (empty($backups)): ?>
            <p>バックアップがありません。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ファイル名</th>
                        <th>サイズ</th>
                        <th>作成日時</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $b): ?>
                        <tr>
                            <td><?php echo h($b['filename']); ?></td>
                            <td><?php echo FileHelper::formatFileSize($b['size']); ?></td>
                            <td><?php echo h($b['date']); ?></td>
                            <td>
                                <div class="actions">
                                    <form method="post" style="display: inline;" onsubmit="return confirm('このバックアップを復元しますか？現在のデータは上書きされます。');">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="filename" value="<?php echo h($b['filename']); ?>">
                                        <button type="submit" class="btn btn-small">復元</button>
                                    </form>
                                    
                                    <a href="../backups/<?php echo h($b['filename']); ?>" class="btn btn-small" download>ダウンロード</a>
                                    
                                    <form method="post" style="display: inline;" onsubmit="return confirm('このバックアップを削除しますか？');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="filename" value="<?php echo h($b['filename']); ?>">
                                        <button type="submit" class="btn btn-danger btn-small">削除</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
