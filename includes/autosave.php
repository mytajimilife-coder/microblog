<?php
/**
 * 自動下書き保存
 * ブラウザのlocalStorageと連携
 */

class AutoSave {
    private $db;
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    
    /**
     * 下書き保存
     */
    public function saveDraft($userId, $postId, $data) {
        $sql = "INSERT INTO auto_saves (user_id, post_id, title, content, saved_data, expires_at)
                VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    content = VALUES(content),
                    saved_data = VALUES(saved_data),
                    updated_at = NOW(),
                    expires_at = DATE_ADD(NOW(), INTERVAL 7 DAY)";
        
        $savedData = json_encode($data);
        
        try {
            $this->db->query($sql, [
                $userId,
                $postId,
                $data['title'] ?? '',
                $data['content'] ?? '',
                $savedData
            ]);
            
            return ['success' => true, 'message' => '下書きを保存しました'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'エラー: ' . $e->getMessage()];
        }
    }
    
    /**
     * 下書き取得
     */
    public function getDraft($userId, $postId) {
        $sql = "SELECT * FROM auto_saves 
                WHERE user_id = ? AND post_id = ? 
                AND expires_at > NOW()
                ORDER BY updated_at DESC 
                LIMIT 1";
        
        $draft = $this->db->fetch($sql, [$userId, $postId]);
        
        if ($draft && $draft['saved_data']) {
            $draft['data'] = json_decode($draft['saved_data'], true);
        }
        
        return $draft;
    }
    
    /**
     * ユーザーの下書き一覧
     */
    public function getUserDrafts($userId, $limit = 10) {
        $sql = "SELECT * FROM auto_saves 
                WHERE user_id = ? 
                AND expires_at > NOW()
                ORDER BY updated_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
    
    /**
     * 下書き削除
     */
    public function deleteDraft($userId, $postId) {
        $sql = "DELETE FROM auto_saves WHERE user_id = ? AND post_id = ?";
        
        try {
            $this->db->query($sql, [$userId, $postId]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * 期限切れ下書き削除
     */
    public function cleanupExpired() {
        $sql = "DELETE FROM auto_saves WHERE expires_at <= NOW()";
        
        try {
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
            return ['success' => true, 'deleted' => $count];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * JavaScriptコード生成
     */
    public static function getJavaScript($postId = 0, $userId = 0) {
        return <<<JS
<script>
(function() {
    const AUTO_SAVE_INTERVAL = 30000; // 30秒
    const STORAGE_KEY = 'microblog_autosave_{$postId}_{$userId}';
    let autoSaveTimer = null;
    
    // フォーム要素取得
    const titleInput = document.querySelector('input[name="title"]');
    const contentInput = document.querySelector('textarea[name="content"]');
    
    if (!titleInput || !contentInput) return;
    
    // 自動保存関数
    function autoSave() {
        const data = {
            title: titleInput.value,
            content: contentInput.value,
            timestamp: new Date().toISOString()
        };
        
        // localStorageに保存
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            
            // サーバーにも保存（オプション）
            fetch('api/autosave.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    post_id: {$postId},
                    user_id: {$userId},
                    data: data
                })
            }).catch(err => console.log('Auto-save failed:', err));
            
            showSaveIndicator();
        } catch (e) {
            console.error('Auto-save error:', e);
        }
    }
    
    // 保存インジケーター表示
    function showSaveIndicator() {
        let indicator = document.getElementById('autosave-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'autosave-indicator';
            indicator.style.cssText = 'position:fixed;top:20px;right:20px;background:#27ae60;color:white;padding:10px 20px;border-radius:5px;z-index:9999;';
            document.body.appendChild(indicator);
        }
        indicator.textContent = '✓ 自動保存しました';
        indicator.style.display = 'block';
        
        setTimeout(() => {
            indicator.style.display = 'none';
        }, 2000);
    }
    
    // 保存データ復元
    function restoreAutoSave() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                
                if (confirm('保存された下書きがあります。復元しますか？\\n\\n保存日時: ' + new Date(data.timestamp).toLocaleString())) {
                    titleInput.value = data.title || '';
                    contentInput.value = data.content || '';
                }
            }
        } catch (e) {
            console.error('Restore error:', e);
        }
    }
    
    // イベントリスナー設定
    titleInput.addEventListener('input', () => {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(autoSave, AUTO_SAVE_INTERVAL);
    });
    
    contentInput.addEventListener('input', () => {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(autoSave, AUTO_SAVE_INTERVAL);
    });
    
    // ページ離脱時に保存
    window.addEventListener('beforeunload', (e) => {
        if (titleInput.value || contentInput.value) {
            autoSave();
        }
    });
    
    // 初期化時に復元確認
    window.addEventListener('load', restoreAutoSave);
})();
</script>
JS;
    }
    
    /**
     * テーブル作成
     */
    public static function createTable($db) {
        if (DB_TYPE === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS auto_saves (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                post_id INT DEFAULT 0,
                title VARCHAR(255),
                content LONGTEXT,
                saved_data LONGTEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                expires_at TIMESTAMP,
                UNIQUE KEY unique_save (user_id, post_id),
                INDEX idx_user_id (user_id),
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS auto_saves (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                post_id INTEGER DEFAULT 0,
                title VARCHAR(255),
                content TEXT,
                saved_data TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP,
                UNIQUE (user_id, post_id)
            );
            CREATE INDEX IF NOT EXISTS idx_autosaves_user ON auto_saves(user_id);
            CREATE INDEX IF NOT EXISTS idx_autosaves_expires ON auto_saves(expires_at)";
        }
        
        try {
            $db->query($sql);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
