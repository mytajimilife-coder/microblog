<?php
/**
 * テーマ管理画面
 */

session_start();

require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';
require_once '../includes/site_settings.php';
require_once '../includes/admin/auth.php';

// ログインチェック
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$pdo = $db->connect();
$settings = new SiteSettings($pdo);

$message = '';

// テーマ変更のリクエスト処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $newTheme = $_POST['theme'];
    
    // バリデーション: 実在するディレクトリか確認
    if (is_dir(__DIR__ . '/../themes/' . $newTheme)) {
        $settings->set('theme', $newTheme);
        $message = 'テーマを「' . htmlspecialchars($newTheme) . '」に更新しました！';
    } else {
        $message = 'エラー: 指定されたテーマが見つかりません。';
    }
}

// 現在のテーマ取得
$currentTheme = $settings->get('theme', 'sleek');

// 利用可能なテーマをスキャン
$themes = [];
$themeDirs = glob(__DIR__ . '/../themes/*', GLOB_ONLYDIR);
foreach ($themeDirs as $dir) {
    if (basename($dir) !== 'shared') { // 共通ファイル用フォルダは除外
        $themes[] = basename($dir);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>テーマ管理 - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 40px; color: #1c1e21; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 20px; font-size: 24px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; background: #e7f3ff; color: #007bff; border: 1px solid #cce5ff; }
        .theme-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .theme-card { border: 2px solid #ddd; border-radius: 12px; padding: 20px; text-align: center; background: #fff; transition: all 0.2s; position: relative; }
        .theme-card.active { border-color: #007bff; background: #f0f7ff; }
        .theme-card h3 { margin-bottom: 15px; text-transform: capitalize; }
        .theme-card .status { font-size: 12px; font-weight: bold; color: #007bff; margin-bottom: 10px; display: block; }
        .btn-select { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-select:hover { background: #0056b3; }
        .btn-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #606770; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-back">← ダッシュボードに戻る</a>
        <h1>🎨 テーマ管理</h1>

        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="theme-grid">
            <?php foreach ($themes as $theme): ?>
                <?php $isActive = ($theme === $currentTheme); ?>
                <div class="theme-card <?php echo $isActive ? 'active' : ''; ?>">
                    <?php if ($isActive): ?>
                        <span class="status">● 使用中</span>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($theme); ?></h3>
                    
                    <form method="POST">
                        <input type="hidden" name="theme" value="<?php echo htmlspecialchars($theme); ?>">
                        <button type="submit" class="btn-select" <?php echo $isActive ? 'disabled style="background:#ccc;cursor:default;"' : ''; ?>>
                            <?php echo $isActive ? '適用済み' : 'このテーマに変更'; ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
