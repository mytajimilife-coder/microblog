<?php
/**
 * Cron Job - 予約投稿自動公開
 * 
 * このスクリプトを定期的に実行して、予約投稿を自動公開します
 * 
 * Cronの設定例（5分ごとに実行）:
 * */5 * * * * php /path/to/microblog/cron/publish_scheduled.php
 * 
 * Windowsタスクスケジューラの設定例:
 * プログラム: C:\php\php.exe
 * 引数: D:\mmorpg\microblog\cron\publish_scheduled.php
 * トリガー: 5分ごと
 */

// CLIからの実行のみ許可
if (php_sapi_name() !== 'cli') {
    die('このスクリプトはコマンドラインからのみ実行できます');
}

// パスの設定
$rootPath = dirname(__DIR__);

require_once $rootPath . '/config/config.php';
require_once $rootPath . '/includes/database.php';
require_once $rootPath . '/includes/model.php';
require_once $rootPath . '/includes/scheduled_posts.php';

// ログファイル
$logFile = $rootPath . '/logs/scheduled_posts.log';
$logDir = dirname($logFile);

// ログディレクトリ作成
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

// ログ関数
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    @file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

try {
    writeLog('予約投稿公開処理を開始します');
    
    // データベース接続
    $db = new Database();
    $pdo = $db->connect();
    
    // 予約投稿管理
    $scheduler = new ScheduledPosts($pdo);
    
    // 予約投稿を公開
    $result = $scheduler->publishScheduledPosts();
    
    if ($result['success']) {
        writeLog($result['message']);
        
        if ($result['published_count'] > 0) {
            writeLog("成功: {$result['published_count']}件の投稿を公開しました");
        } else {
            writeLog('公開する予約投稿はありませんでした');
        }
    } else {
        writeLog('エラー: ' . $result['message']);
    }
    
    writeLog('処理完了');
    
} catch (Exception $e) {
    writeLog('致命的エラー: ' . $e->getMessage());
    exit(1);
}

exit(0);
?>
