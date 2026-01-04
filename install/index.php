<?php
/**
 * Microblog インストールウィザード
 * ブログシステムのインストールガイド
 */

// インストール済みチェック
if (file_exists(__DIR__ . '/../config/database.php')) {
    header('Location: ../index.php');
    exit;
}

$step = (int)($_GET['step'] ?? 1);
$errors = [];
$success = false;

// ステップ別処理
switch ($step) {
    case 1:
        // システム要件チェック
        $requirements = checkRequirements();
        if (isset($_POST['next'])) {
            $failedRequirements = array_filter($requirements, function($req) {
                return !$req['status'];
            });
            
            if (empty($failedRequirements)) {
                header('Location: index.php?step=2');
                exit;
            } else {
                $errors[] = 'システム要件を満たしていません';
            }
        }
        break;
        
    case 2:
        // データベース設定
        if ($_POST) {
            $dbConfig = [
                'type' => $_POST['db_type'] ?? '',
                'host' => $_POST['db_host'] ?? '',
                'name' => $_POST['db_name'] ?? '',
                'user' => $_POST['db_user'] ?? '',
                'pass' => $_POST['db_pass'] ?? '',
                'charset' => $_POST['db_charset'] ?? 'utf8mb4'
            ];
            
            // バリデーション
            if (empty($dbConfig['type']) || !in_array($dbConfig['type'], ['mysql', 'postgresql'])) {
                $errors[] = 'データベースタイプを選択してください';
            }
            
            if (empty($dbConfig['host'])) {
                $errors[] = 'データベースホストを入力してください';
            }
            
            if (empty($dbConfig['name'])) {
                $errors[] = 'データベース名を入力してください';
            }
            
            if (empty($dbConfig['user'])) {
                $errors[] = 'データベースユーザー名を入力してください';
            }
            
            if (empty($errors)) {
                // データベース接続テスト
                try {
                    $dsn = $dbConfig['type'] === 'mysql' 
                        ? "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}"
                        : "pgsql:host={$dbConfig['host']};dbname={$dbConfig['name']}";
                    
                    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // 設定を保存
                    $saveResult = saveDatabaseConfig($dbConfig);
                    
                    if ($saveResult === false && isset($_SESSION['manual_config'])) {
                        // 権限エラー - 手動設定画面へ
                        header('Location: index.php?step=2.5');
                        exit;
                    }
                    
                    header('Location: index.php?step=3');
                    exit;
                    
                } catch (Exception $e) {
                    $errors[] = 'データベース接続に失敗しました: ' . $e->getMessage();
                }
            }
        }
        break;
        
    case 3:
        // サイト設定
        if ($_POST) {
            $siteConfig = [
                'site_name' => trim($_POST['site_name'] ?? ''),
                'site_description' => trim($_POST['site_description'] ?? ''),
                'admin_username' => trim($_POST['admin_username'] ?? ''),
                'admin_email' => trim($_POST['admin_email'] ?? ''),
                'admin_password' => $_POST['admin_password'] ?? '',
                'admin_password_confirm' => $_POST['admin_password_confirm'] ?? ''
            ];
            
            // バリデーション
            if (empty($siteConfig['site_name'])) {
                $errors[] = 'サイト名を入力してください';
            }
            
            if (empty($siteConfig['admin_username'])) {
                $errors[] = '管理者ユーザー名を入力してください';
            }
            
            if (empty($siteConfig['admin_email'])) {
                $errors[] = '管理者メールアドレスを入力してください';
            } elseif (!filter_var($siteConfig['admin_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = '有効なメールアドレスを入力してください';
            }
            
            if (empty($siteConfig['admin_password'])) {
                $errors[] = '管理者パスワードを入力してください';
            } elseif (strlen($siteConfig['admin_password']) < 6) {
                $errors[] = 'パスワードは6文字以上で入力してください';
            }
            
            if ($siteConfig['admin_password'] !== $siteConfig['admin_password_confirm']) {
                $errors[] = 'パスワードが一致しません';
            }
            
            if (empty($errors)) {
                // データベース初期化とサイト設定
                try {
                    initializeDatabase($siteConfig);
                    $success = true;
                } catch (Exception $e) {
                    $errors[] = 'インストール中にエラーが発生しました: ' . $e->getMessage();
                }
            }
        }
        break;
}

/**
 * システム要件チェック
 */
function checkRequirements() {
    return [
        'php_version' => [
            'name' => 'PHP 7.4 以上',
            'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'current' => PHP_VERSION
        ],
        'pdo' => [
            'name' => 'PDO 拡張',
            'status' => extension_loaded('pdo'),
            'current' => extension_loaded('pdo') ? 'インストール済み' : '未インストール'
        ],
        'pdo_mysql' => [
            'name' => 'PDO MySQL 拡張',
            'status' => extension_loaded('pdo_mysql'),
            'current' => extension_loaded('pdo_mysql') ? 'インストール済み' : '未インストール'
        ],
        'pdo_pgsql' => [
            'name' => 'PDO PostgreSQL 拡張',
            'status' => extension_loaded('pdo_pgsql'),
            'current' => extension_loaded('pdo_pgsql') ? 'インストール済み' : '未インストール'
        ],
        'gd' => [
            'name' => 'GD 拡張（画像処理）',
            'status' => extension_loaded('gd'),
            'current' => extension_loaded('gd') ? 'インストール済み' : '未インストール'
        ],
        'file_upload' => [
            'name' => 'ファイルアップロード',
            'status' => ini_get('file_uploads'),
            'current' => ini_get('file_uploads') ? '有効' : '無効'
        ],
        'write_permissions' => [
            'name' => '書き込み権限',
            'status' => is_writable(__DIR__ . '/../config') && is_writable(__DIR__ . '/../uploads'),
            'current' => (is_writable(__DIR__ . '/../config') && is_writable(__DIR__ . '/../uploads')) ? '書き込み可能' : '書き込み不可'
        ]
    ];
}

/**
 * データベース設定保存
 */
function saveDatabaseConfig($config) {
    $configContent = "<?php
/**
 * データベース設定
 * インストール時に自動生成
 */

define('DB_TYPE', '{$config['type']}');
define('DB_HOST', '{$config['host']}');
define('DB_NAME', '{$config['name']}');
define('DB_USER', '{$config['user']}');
define('DB_PASS', '{$config['pass']}');
define('DB_CHARSET', '{$config['charset']}');
?>";
    
    $configPath = __DIR__ . '/../config/database.php';
    
    // ディレクトリが存在しない場合は作成を試みる
    $configDir = dirname($configPath);
    if (!is_dir($configDir)) {
        @mkdir($configDir, 0755, true);
    }
    
    // 書き込み権限をチェック
    if (!is_writable($configDir)) {
        // 権限がない場合、ユーザーに手動作成を促す
        $_SESSION['manual_config'] = $configContent;
        $_SESSION['config_path'] = $configPath;
        return false;
    }
    
    // ファイルに書き込み
    $result = @file_put_contents($configPath, $configContent);
    
    if ($result === false) {
        // 書き込み失敗時も手動作成を促す
        $_SESSION['manual_config'] = $configContent;
        $_SESSION['config_path'] = $configPath;
        return false;
    }
    
    return true;
}

/**
 * データベース初期化とサイト設定
 */
function initializeDatabase($siteConfig) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../includes/database.php';
    require_once __DIR__ . '/../includes/model.php';
    
    $db = new Database();
    $pdo = $db->connect();
    
    // テーブル作成
    $db->initialize();
    
    // デフォルトデータ挿入
    $db->insertDefaultData();
    
    // サイト設定
    $settingModel = new Setting($pdo);
    $settingModel->set('site_name', $siteConfig['site_name'], 'サイト名');
    $settingModel->set('site_description', $siteConfig['site_description'], 'サイト説明');
    $settingModel->set('admin_email', $siteConfig['admin_email'], '管理者メールアドレス');
    
    // 管理者ユーザー作成
    $userModel = new User($pdo);
    $hashedPassword = password_hash($siteConfig['admin_password'], PASSWORD_DEFAULT);
    
    $userModel->create([
        'username' => $siteConfig['admin_username'],
        'email' => $siteConfig['admin_email'],
        'password_hash' => $hashedPassword,
        'display_name' => '管理者',
        'role' => 'admin',
        'status' => 'active'
    ]);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Microblog インストール - ステップ <?php echo $step; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Hiragino Sans', 'Yu Gothic', 'Meiryo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .install-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }
        
        .install-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .install-title {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .install-subtitle {
            opacity: 0.8;
            font-size: 14px;
        }
        
        .install-content {
            padding: 30px;
        }
        
        .step-indicator {
            display: flex;
            margin-bottom: 30px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #ecf0f1;
            margin: 0 2px;
            border-radius: 5px;
            font-size: 12px;
            position: relative;
        }
        
        .step.active {
            background: #3498db;
            color: white;
        }
        
        .step.completed {
            background: #27ae60;
            color: white;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .requirements {
            margin-bottom: 20px;
        }
        
        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .requirement-item:last-child {
            border-bottom: none;
        }
        
        .status-ok {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-error {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
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
        
        .btn-block {
            width: 100%;
            text-align: center;
        }
        
        .form-actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .success-message {
            text-align: center;
            padding: 30px;
        }
        
        .success-icon {
            font-size: 48px;
            color: #27ae60;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1 class="install-title">Microblog インストール <span style="font-size: 0.5em; opacity: 0.7;">Beta v1.0.0</span></h1>
            <p class="install-subtitle">日本語専用ブログシステム インストールウィザード</p>
        </div>
        
        <div class="install-content">
            <?php if ($success): ?>
                <div class="success-message">
                    <div class="success-icon">✓</div>
                    <h2>インストールが完了しました！</h2>
                    <p style="margin: 20px 0;">Microblog のインストールが正常に完了しました。</p>
                    <p><strong>デフォルトログイン情報:</strong></p>
                    <p>ユーザー名: <?php echo htmlspecialchars($_POST['admin_username']); ?><br>
                    パスワード: <?php echo htmlspecialchars($_POST['admin_password']); ?></p>
                    <div class="form-actions" style="margin-top: 30px;">
                        <a href="../index.php" class="btn btn-success">サイトを見る</a>
                        <a href="../admin/" class="btn">管理パネルへ</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="step-indicator">
                    <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">ステップ1<br>システム要件</div>
                    <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">ステップ2<br>データベース設定</div>
                    <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">ステップ3<br>サイト設定</div>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo h($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($step === 1): ?>
                    <h3>システム要件チェック</h3>
                    <p>インストール前にシステム要件を満たしているか確認します。</p>
                    
                    <div class="requirements">
                        <?php foreach ($requirements as $key => $req): ?>
                            <div class="requirement-item">
                                <span><?php echo h($req['name']); ?></span>
                                <span class="<?php echo $req['status'] ? 'status-ok' : 'status-error'; ?>">
                                    <?php echo $req['status'] ? '✓' : '✗'; ?>
                                    <?php echo h($req['current']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <form method="post">
                        <div class="form-actions">
                            <button type="submit" name="next" class="btn btn-success">次へ</button>
                        </div>
                    </form>
                    
                <?php elseif ($step === 2): ?>
                    <h3>データベース設定</h3>
                    <p>データベース接続情報を入力してください。</p>
                    
                    <form method="post">
                        <div class="form-group">
                            <label class="form-label">データベースタイプ</label>
                            <select name="db_type" class="form-select" required>
                                <option value="">選択してください</option>
                                <option value="mysql" <?php echo ($_POST['db_type'] ?? '') === 'mysql' ? 'selected' : ''; ?>>MySQL</option>
                                <option value="postgresql" <?php echo ($_POST['db_type'] ?? '') === 'postgresql' ? 'selected' : ''; ?>>PostgreSQL</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">データベースホスト</label>
                            <input type="text" name="db_host" class="form-input" 
                                   value="<?php echo h($_POST['db_host'] ?? 'localhost'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">データベース名</label>
                            <input type="text" name="db_name" class="form-input" 
                                   value="<?php echo h($_POST['db_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">データベースユーザー名</label>
                            <input type="text" name="db_user" class="form-input" 
                                   value="<?php echo h($_POST['db_user'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">データベースパスワード</label>
                            <input type="password" name="db_pass" class="form-input" 
                                   value="<?php echo h($_POST['db_pass'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn">接続テストと次へ</button>
                        </div>
                    </form>
                    
                <?php elseif ($step === 2.5): ?>
                    <h3>手動設定が必要です</h3>
                    <p>書き込み権限がないため、以下のファイルを手動で作成してください。</p>
                    
                    <div class="alert alert-error">
                        <strong>ファイルパス:</strong> <?php echo h($_SESSION['config_path'] ?? ''); ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">以下の内容をコピーして、上記のパスにファイルを作成してください:</label>
                        <textarea class="form-input" rows="15" readonly style="font-family: monospace; font-size: 12px;"><?php echo h($_SESSION['manual_config'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="alert alert-success">
                        <strong>手順:</strong><br>
                        1. 上記のテキストをコピー<br>
                        2. テキストエディタで新規ファイルを作成<br>
                        3. コピーした内容を貼り付け<br>
                        4. 指定されたパスに保存<br>
                        5. 下のボタンをクリックして続行
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php?step=3" class="btn btn-success">ファイルを作成しました - 次へ</a>
                    </div>
                    
                <?php elseif ($step === 3): ?>
                    <h3>サイト設定</h3>
                    <p>サイトの基本設定と管理者アカウントを作成します。</p>
                    
                    <form method="post">
                        <div class="form-group">
                            <label class="form-label">サイト名</label>
                            <input type="text" name="site_name" class="form-input" 
                                   value="<?php echo h($_POST['site_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">サイト説明</label>
                            <textarea name="site_description" class="form-input" rows="3"><?php echo h($_POST['site_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">管理者ユーザー名</label>
                            <input type="text" name="admin_username" class="form-input" 
                                   value="<?php echo h($_POST['admin_username'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">管理者メールアドレス</label>
                            <input type="email" name="admin_email" class="form-input" 
                                   value="<?php echo h($_POST['admin_email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">管理者パスワード</label>
                            <input type="password" name="admin_password" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">パスワード確認</label>
                            <input type="password" name="admin_password_confirm" class="form-input" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">インストール完了</button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>