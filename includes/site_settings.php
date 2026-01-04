<?php
/**
 * サイト設定・メンテナンス管理
 */

class SiteSettings {
    private $db;
    private $cache;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        require_once __DIR__ . '/cache.php';
        $this->cache = new SimpleCache();
    }

    /**
     * メンテナンスモードのチェック
     */
    public function isMaintenanceMode() {
        $mode = $this->get('maintenance_mode', '0');
        if ($mode === '1') {
            // 管理者は除外
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 設定値を取得（キャッシュ対応）
     */
    public function get($key, $default = null) {
        $cacheKey = 'setting_' . $key;
        
        return $this->cache->remember($cacheKey, function() use ($key, $default) {
            $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
            $result = $this->db->fetch($sql, [$key]);
            return $result ? $result['setting_value'] : $default;
        }, 3600);
    }

    /**
     * 設定値を保存
     */
    public function set($key, $value) {
        $sql = "INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        
        if (DB_TYPE === 'postgresql') {
            $sql = "INSERT INTO settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value";
        }

        $result = $this->db->query($sql, [$key, $value]);
        
        // キャッシュを削除
        $this->cache->delete('setting_' . $key);
        
        return $result;
    }

    /**
     * メンテナンス用メッセージ
     */
    public function displayMaintenancePage() {
        $message = $this->get('maintenance_message', '現在メンテナンス中です。しばらくお待ちください。');
        
        http_response_code(503);
        include __DIR__ . '/../themes/default/maintenance.php';
        exit;
    }
}
?>
