<?php
/**
 * データベース抽象化レイヤー
 * MySQL および PostgreSQL 両方をサポート
 */

class Database {
    private $connection;
    private $type;
    private $host;
    private $database;
    private $username;
    private $password;
    private $charset;
    
    public function __construct() {
        $this->type = DB_TYPE;
        $this->host = DB_HOST;
        $this->database = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }
    
    /**
     * データベース接続
     */
    public function connect() {
        try {
            if ($this->type === 'mysql') {
                $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
            } else if ($this->type === 'postgresql') {
                $dsn = "pgsql:host={$this->host};dbname={$this->database};options='--client_encoding=UTF8'";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
            } else {
                throw new Exception('サポートされていないデータベースタイプ: ' . $this->type);
            }
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            return $this->connection;
            
        } catch (PDOException $e) {
            throw new Exception('データベース接続エラー: ' . $e->getMessage());
        }
    }
    
    /**
     * 接続テスト
     */
    public function testConnection() {
        try {
            $conn = $this->connect();
            if ($this->type === 'mysql') {
                $stmt = $conn->query('SELECT VERSION()');
            } else {
                $stmt = $conn->query('SELECT version()');
            }
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * SQLクエリ実行
     */
    public function query($sql, $params = []) {
        try {
            $conn = $this->connection ?? $this->connect();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('クエリ実行エラー: ' . $e->getMessage());
        }
    }
    
    /**
     * データ取得（単一行）
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * データ取得（全行）
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * 挿入操作
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        if ($this->type === 'mysql') {
            return $this->connection->lastInsertId();
        } else {
            // PostgreSQL の場合
            $stmt = $this->query("SELECT LASTVAL()");
            $result = $stmt->fetch();
            return $result['lastval'] ?? null;
        }
    }
    
    /**
     * 更新操作
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * 削除操作
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * テーブル存在確認
     */
    public function tableExists($tableName) {
        if ($this->type === 'mysql') {
            $sql = "SHOW TABLES LIKE ?";
        } else {
            $sql = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = ?)";
        }
        
        $result = $this->fetch($sql, [$tableName]);
        
        if ($this->type === 'mysql') {
            return $result !== false;
        } else {
            return $result['exists'] ?? false;
        }
    }
    
    /**
     * データベース初期化（テーブル作成）
     */
    public function initialize() {
        $this->connect();
        $this->createTables();
        $this->createIndexes();
        $this->insertDefaultData();
    }
    
    /**
     * テーブル作成
     */
    private function createTables() {
        $sql = $this->getTableCreationSQL();
        
        // SQLを分割して実行
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $this->query($statement);
                } catch (Exception $e) {
                    // 既存テーブルの場合は無視
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
        }
        
        // 追加機能のテーブル作成
        require_once __DIR__ . '/media_library.php';
        require_once __DIR__ . '/autosave.php';
        require_once __DIR__ . '/security_audit.php';
        require_once __DIR__ . '/contact_system.php';
        require_once __DIR__ . '/post_meta.php';
        require_once __DIR__ . '/api_manager.php';
        require_once __DIR__ . '/security/ip_manager.php';
        
        MediaLibrary::createTable($this);
        AutoSave::createTable($this);
        SecurityAudit::createTables($this);
        ContactSystem::createTable($this);
        PostMeta::createTable($this);
        APIManager::createTable($this);
        IPManager::createTable($this);
    }
    
    /**
     * テーブル作成SQL取得
     */
    private function getTableCreationSQL() {
        if ($this->type === 'mysql') {
            return $this->getMySQLTableSQL();
        } else {
            return $this->getPostgreSQLTableSQL();
        }
    }
    
    /**
     * MySQLテーブル作成SQL
     */
    private function getMySQLTableSQL() {
        return "
        CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            excerpt TEXT,
            slug VARCHAR(255) UNIQUE NOT NULL,
            status ENUM('draft', 'published', 'private', 'scheduled') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            published_at TIMESTAMP NULL,
            scheduled_publish_at TIMESTAMP NULL,
            author_id INT,
            featured_image VARCHAR(255),
            view_count INT DEFAULT 0,
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_slug (slug),
            INDEX idx_scheduled (scheduled_publish_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            parent_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
            INDEX idx_parent (parent_id),
            INDEX idx_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            slug VARCHAR(50) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS post_categories (
            post_id INT,
            category_id INT,
            PRIMARY KEY (post_id, category_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS post_tags (
            post_id INT,
            tag_id INT,
            PRIMARY KEY (post_id, tag_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            display_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'editor', 'author') DEFAULT 'author',
            avatar VARCHAR(255),
            bio TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value LONGTEXT,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_setting_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            parent_id INT NULL,
            author_name VARCHAR(100) NOT NULL,
            author_email VARCHAR(100) NOT NULL,
            author_url VARCHAR(255),
            author_ip VARCHAR(45),
            content TEXT NOT NULL,
            status ENUM('approved', 'pending', 'spam') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
            INDEX idx_post_id (post_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
    
    /**
     * PostgreSQLテーブル作成SQL
     */
    private function getPostgreSQLTableSQL() {
        return "
        CREATE TABLE IF NOT EXISTS posts (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            excerpt TEXT,
            slug VARCHAR(255) UNIQUE NOT NULL,
            status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'private', 'scheduled')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            published_at TIMESTAMP NULL,
            scheduled_publish_at TIMESTAMP NULL,
            author_id INTEGER,
            featured_image VARCHAR(255),
            view_count INTEGER DEFAULT 0
        );

        CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status);
        CREATE INDEX IF NOT EXISTS idx_posts_created_at ON posts(created_at);
        CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug);
        CREATE INDEX IF NOT EXISTS idx_posts_scheduled ON posts(scheduled_publish_at);

        CREATE TABLE IF NOT EXISTS categories (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            parent_id INTEGER NULL REFERENCES categories(id) ON DELETE SET NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX IF NOT EXISTS idx_categories_parent ON categories(parent_id);
        CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories(slug);

        CREATE TABLE IF NOT EXISTS tags (
            id SERIAL PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            slug VARCHAR(50) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX IF NOT EXISTS idx_tags_slug ON tags(slug);

        CREATE TABLE IF NOT EXISTS post_categories (
            post_id INTEGER,
            category_id INTEGER,
            PRIMARY KEY (post_id, category_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS post_tags (
            post_id INTEGER,
            tag_id INTEGER,
            PRIMARY KEY (post_id, tag_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            display_name VARCHAR(100) NOT NULL,
            role VARCHAR(20) DEFAULT 'author' CHECK (role IN ('admin', 'editor', 'author')),
            avatar VARCHAR(255),
            bio TEXT,
            status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL
        );

        CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
        CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
        CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

        CREATE TABLE IF NOT EXISTS settings (
            id SERIAL PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX IF NOT EXISTS idx_settings_key ON settings(setting_key);

        CREATE TABLE IF NOT EXISTS comments (
            id SERIAL PRIMARY KEY,
            post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
            parent_id INTEGER NULL REFERENCES comments(id) ON DELETE CASCADE,
            author_name VARCHAR(100) NOT NULL,
            author_email VARCHAR(100) NOT NULL,
            author_url VARCHAR(255),
            author_ip VARCHAR(45),
            content TEXT NOT NULL,
            status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('approved', 'pending', 'spam')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX IF NOT EXISTS idx_comments_post_id ON comments(post_id);
        CREATE INDEX IF NOT EXISTS idx_comments_status ON comments(status);
        CREATE INDEX IF NOT EXISTS idx_comments_created_at ON comments(created_at);
        ";
    }
    
    /**
     * インデックス作成
     */
    private function createIndexes() {
        // 追加のインデックスが必要な場合はここに追加
        // 既にテーブル作成時に大部分は作成済み
    }
    
    /**
     * デフォルトデータ挿入
     */
    public function insertDefaultData() {
        // デフォルト管理者ユーザー
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $this->query(
            "INSERT IGNORE INTO users (username, email, password_hash, display_name, role) VALUES (?, ?, ?, ?, ?)",
            ['admin', 'admin@example.com', $adminPassword, '管理者', 'admin']
        );
        
        // デフォルト設定
        $defaultSettings = [
            ['site_name', 'Microblog', 'サイト名'],
            ['site_description', '日本語専用ブログシステム', 'サイト説明'],
            ['posts_per_page', '10', 'ページあたりの投稿数'],
            ['theme', 'default', '使用テーマ'],
            ['language', 'ja', 'デフォルト言語'],
            ['comments_enabled', '1', 'コメント機能有効'],
            ['comments_moderation', '1', 'コメント承認制'],
        ];
        
        foreach ($defaultSettings as $setting) {
            $this->query(
                "INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES (?, ?, ?)",
                $setting
            );
        }
    }
    
    /**
     * 切断
     */
    public function disconnect() {
        $this->connection = null;
    }
}
?>