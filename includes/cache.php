<?php
/**
 * シンプルキャッシュシステム
 * ファイルベースのキャッシュ（PHP 7.4+対応）
 */

class SimpleCache {
    private $cacheDir;
    private $defaultTTL = 3600; // 1時間
    
    public function __construct($cacheDir = null) {
        if ($cacheDir === null) {
            $cacheDir = __DIR__ . '/../cache/';
        }
        
        $this->cacheDir = rtrim($cacheDir, '/') . '/';
        
        // キャッシュディレクトリ作成
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * キャッシュ取得
     */
    public function get($key, $default = null) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $data = @file_get_contents($filename);
        if ($data === false) {
            return $default;
        }
        
        $cache = @unserialize($data);
        if ($cache === false) {
            return $default;
        }
        
        // 有効期限チェック
        if (isset($cache['expires']) && $cache['expires'] < time()) {
            $this->delete($key);
            return $default;
        }
        
        return $cache['value'] ?? $default;
    }
    
    /**
     * キャッシュ保存
     */
    public function set($key, $value, $ttl = null) {
        if ($ttl === null) {
            $ttl = $this->defaultTTL;
        }
        
        $filename = $this->getFilename($key);
        
        $cache = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        $data = serialize($cache);
        
        return @file_put_contents($filename, $data, LOCK_EX) !== false;
    }
    
    /**
     * キャッシュ削除
     */
    public function delete($key) {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return @unlink($filename);
        }
        
        return true;
    }
    
    /**
     * キャッシュ存在確認
     */
    public function has($key) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        // 有効期限チェック
        $value = $this->get($key);
        return $value !== null;
    }
    
    /**
     * 全キャッシュクリア
     */
    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        
        if ($files === false) {
            return false;
        }
        
        $cleared = 0;
        foreach ($files as $file) {
            if (@unlink($file)) {
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * 期限切れキャッシュ削除
     */
    public function cleanup() {
        $files = glob($this->cacheDir . '*.cache');
        
        if ($files === false) {
            return 0;
        }
        
        $cleaned = 0;
        foreach ($files as $file) {
            $data = @file_get_contents($file);
            if ($data === false) {
                continue;
            }
            
            $cache = @unserialize($data);
            if ($cache === false) {
                continue;
            }
            
            if (isset($cache['expires']) && $cache['expires'] < time()) {
                if (@unlink($file)) {
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * キャッシュファイル名生成
     */
    private function getFilename($key) {
        $hash = md5($key);
        return $this->cacheDir . $hash . '.cache';
    }
    
    /**
     * キャッシュ統計
     */
    public function getStats() {
        $files = glob($this->cacheDir . '*.cache');
        
        if ($files === false) {
            return [
                'total' => 0,
                'size' => 0,
                'expired' => 0
            ];
        }
        
        $total = count($files);
        $size = 0;
        $expired = 0;
        
        foreach ($files as $file) {
            $size += filesize($file);
            
            $data = @file_get_contents($file);
            if ($data !== false) {
                $cache = @unserialize($data);
                if ($cache !== false && isset($cache['expires']) && $cache['expires'] < time()) {
                    $expired++;
                }
            }
        }
        
        return [
            'total' => $total,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'expired' => $expired,
            'active' => $total - $expired
        ];
    }
    
    /**
     * バイト数フォーマット
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Remember パターン（キャッシュがなければ生成）
     */
    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
}
?>
