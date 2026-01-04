<?php
/**
 * データベースバックアップ機能
 */

class DatabaseBackup {
    private $db;
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->db = new Database();
    }
    
    /**
     * バックアップ作成
     */
    public function create($filename = null) {
        if ($filename === null) {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        }
        
        $backupDir = __DIR__ . '/../backups/';
        
        // バックアップディレクトリ作成
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }
        
        $filepath = $backupDir . $filename;
        
        // テーブル一覧取得
        $tables = $this->getTables();
        
        $output = "-- Microblog Database Backup\n";
        $output .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: " . DB_NAME . "\n\n";
        
        foreach ($tables as $table) {
            $output .= $this->exportTable($table);
        }
        
        // ファイルに保存
        $result = @file_put_contents($filepath, $output);
        
        if ($result === false) {
            return ['success' => false, 'message' => 'バックアップファイルの作成に失敗しました'];
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * テーブル一覧取得
     */
    private function getTables() {
        if (DB_TYPE === 'mysql') {
            $stmt = $this->pdo->query("SHOW TABLES");
        } else {
            $stmt = $this->pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        }
        
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        return $tables;
    }
    
    /**
     * テーブルエクスポート
     */
    private function exportTable($table) {
        $output = "\n-- Table: {$table}\n";
        $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
        
        // テーブル構造取得
        if (DB_TYPE === 'mysql') {
            $stmt = $this->pdo->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $output .= $row['Create Table'] . ";\n\n";
        }
        
        // データ取得
        $stmt = $this->pdo->query("SELECT * FROM `{$table}`");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns = array_keys($row);
            $values = array_values($row);
            
            // 値をエスケープ
            $escapedValues = array_map(function($value) {
                if ($value === null) {
                    return 'NULL';
                }
                return "'" . addslashes($value) . "'";
            }, $values);
            
            $output .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
        }
        
        $output .= "\n";
        
        return $output;
    }
    
    /**
     * バックアップ一覧取得
     */
    public function list() {
        $backupDir = __DIR__ . '/../backups/';
        
        if (!is_dir($backupDir)) {
            return [];
        }
        
        $files = glob($backupDir . '*.sql');
        $backups = [];
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'size' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        // 日付順にソート
        usort($backups, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        
        return $backups;
    }
    
    /**
     * バックアップ復元
     */
    public function restore($filename) {
        $backupDir = __DIR__ . '/../backups/';
        $filepath = $backupDir . $filename;
        
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'バックアップファイルが見つかりません'];
        }
        
        $sql = file_get_contents($filepath);
        
        if ($sql === false) {
            return ['success' => false, 'message' => 'バックアップファイルの読み込みに失敗しました'];
        }
        
        try {
            // SQLを実行
            $this->pdo->exec($sql);
            
            return ['success' => true, 'message' => 'バックアップを復元しました'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '復元中にエラーが発生しました: ' . $e->getMessage()];
        }
    }
    
    /**
     * バックアップ削除
     */
    public function delete($filename) {
        $backupDir = __DIR__ . '/../backups/';
        $filepath = $backupDir . $filename;
        
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'バックアップファイルが見つかりません'];
        }
        
        if (@unlink($filepath)) {
            return ['success' => true, 'message' => 'バックアップを削除しました'];
        } else {
            return ['success' => false, 'message' => 'バックアップの削除に失敗しました'];
        }
    }
}
?>
