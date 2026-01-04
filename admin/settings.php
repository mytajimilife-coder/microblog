<?php
/**
 * 統合設定センター
 * サイトの全機能をGUIからコントロール
 */

session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';
require_once '../includes/site_settings.php';
require_once '../includes/admin/auth.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$db = new Database();
$pdo = $db->connect();
$settings = new SiteSettings($pdo);
$message = '';

// 設定保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['s'] as $key => $value) {
        $settings->set($key, $value);
    }
    
    // キャッシュクリアの個別処理
    if (isset($_POST['clear_cache'])) {
        require_once '../includes/cache.php';
        $cache = new SimpleCache();
        $cache->clear();
        $message = '設定を保存し、キャッシュをクリアしました！';
    } else {
        $message = '設定を正常に保存しました。';
    }
}

// 現在の設定値を取得
$data = [
    'site_name' => $settings->get('site_name', SITE_NAME),
    'site_description' => $settings->get('site_description', SITE_DESCRIPTION),
    'maintenance_mode' => $settings->get('maintenance_mode', '0'),
    'maintenance_message' => $settings->get('maintenance_message', '現在メンテナンス中です。'),
    'img_max_width' => $settings->get('img_max_width', '1920'),
    'img_quality' => $settings->get('img_quality', '85'),
    'social_share_enabled' => $settings->get('social_share_enabled', '1'),
    'comments_enabled' => $settings->get('comments_enabled', '1'),
    'seo_keywords' => $settings->get('seo_keywords', ''),
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>基本設定 - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 240px; background: #2c3e50; color: white; min-height: 100vh; padding: 20px; }
        .main { flex: 1; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 25px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #34495e; }
        input[type="text"], textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; }
        .btn-save { background: #27ae60; color: white; border: none; padding: 15px 30px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn-save:hover { background: #219150; }
        .alert { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .section-title { font-size: 18px; color: #3498db; margin: 30px 0 15px 0; border-left: 4px solid #3498db; padding-left: 10px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>管理者パネル</h2>
        <p><a href="index.php" style="color:white;">🏠 ダッシュボード</a></p>
        <p><a href="themes.php" style="color:white;">🎨 テーマ管理</a></p>
        <p><a href="firewall.php" style="color:white;">🛡️ セキュリティ</a></p>
        <p><a href="contacts.php" style="color:white;">📧 お問い合わせ</a></p>
    </div>
    <div class="main">
        <div class="card">
            <h1>⚙️ 一般・高度な設定</h1>
            <?php if ($message): ?><div class="alert"><?php echo $message; ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="section-title">基本情報</div>
                <div class="form-group">
                    <label>サイト名</label>
                    <input type="text" name="s[site_name]" value="<?php echo htmlspecialchars($data['site_name']); ?>">
                </div>
                <div class="form-group">
                    <label>サイト説明 (SEO用)</label>
                    <textarea name="s[site_description]"><?php echo htmlspecialchars($data['site_description']); ?></textarea>
                </div>

                <div class="section-title">運用制限</div>
                <div class="form-group">
                    <label>メンテナンスモード</label>
                    <select name="s[maintenance_mode]">
                        <option value="0" <?php echo $data['maintenance_mode'] == '0' ? 'selected' : ''; ?>>公開中 (通常)</option>
                        <option value="1" <?php echo $data['maintenance_mode'] == '1' ? 'selected' : ''; ?>>メンテナンス中 (管理者のみアクセス可)</option>
                    </select>
                </div>

                <div class="section-title">画像・メディア最適化</div>
                <div class="form-group" style="display:flex; gap:20px;">
                    <div style="flex:1;">
                        <label>最大画像幅 (px)</label>
                        <input type="text" name="s[img_max_width]" value="<?php echo htmlspecialchars($data['img_max_width']); ?>">
                    </div>
                    <div style="flex:1;">
                        <label>圧縮品質 (1-100)</label>
                        <input type="text" name="s[img_quality]" value="<?php echo htmlspecialchars($data['img_quality']); ?>">
                    </div>
                </div>

                <div class="section-title">システムメンテナンス</div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="clear_cache" value="1"> 保存時にシステムキャッシュをクリアする
                    </label>
                    <p style="font-size:12px; color:#7f8c8d;">※テーマの変更や設定が反映されない場合にチェックしてください。</p>
                </div>

                <button type="submit" class="btn-save">設定を保存する</button>
            </form>
        </div>
    </div>
</body>
</html>
