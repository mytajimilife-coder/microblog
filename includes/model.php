<?php
/**
 * モデルクラス
 * データベース操作の基本モデル
 */

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    
    /**
     * 全レコード取得
     */
    public function all($orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * IDでレコード取得
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * 条件でレコード取得
     */
    public function findBy($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return $this->db->fetch($sql, [$value]);
    }
    
    /**
     * 複数条件でレコード取得
     */
    public function findWhere($conditions) {
        $where = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where);
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * 複数条件で検索（複数行）
     */
    public function where($conditions, $params = [], $orderBy = null, $limit = null) {
        $where = [];
        
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where);
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * ページネーション対応検索
     */
    public function paginate($page = 1, $perPage = 10, $conditions = [], $orderBy = null) {
        $offset = ($page - 1) * $perPage;
        
        // カウントクエリ
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = ?";
                $params[] = $value;
            }
            $countSql .= " WHERE " . implode(' AND ', $where);
        }
        
        $total = $this->db->fetch($countSql, $params)['total'];
        $totalPages = ceil($total / $perPage);
        
        // データ取得クエリ
        $dataSql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = ?";
                $params[] = $value;
            }
            $dataSql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $dataSql .= " ORDER BY {$orderBy}";
        }
        
        $dataSql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $data = $this->db->fetchAll($dataSql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }
    
    /**
     * 新規作成
     */
    public function create($data) {
        // fillableチェック
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }
        
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * 更新
     */
    public function update($id, $data) {
        // fillableチェック
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }
        
        return $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }
    
    /**
     * 削除
     */
    public function delete($id) {
        return $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }
    
    /**
     * 件数取得
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->db->fetch($sql, $params);
        return (int) $result['total'];
    }
    
    /**
     * 存在確認
     */
    public function exists($id) {
        return $this->find($id) !== false;
    }
}

/**
 * 投稿モデル
 */
class Post extends BaseModel {
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'excerpt', 'slug', 'status', 'published_at', 'author_id', 'featured_image'];
    
    /**
     * 公開済み投稿取得
     */
    public function getPublished($page = 1, $perPage = 10) {
        return $this->paginate($page, $perPage, ['status' => 'published'], 'created_at DESC');
    }
    
    /**
     * 投稿とカテゴリー取得
     */
    public function getWithCategories($id) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.display_name as author_name
                FROM posts p
                LEFT JOIN post_categories pc ON p.id = pc.post_id
                LEFT JOIN categories c ON pc.category_id = c.id
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * カテゴリー別投稿取得
     */
    public function getByCategory($categorySlug, $page = 1, $perPage = 10) {
        $sql = "SELECT DISTINCT p.*
                FROM posts p
                INNER JOIN post_categories pc ON p.id = pc.post_id
                INNER JOIN categories c ON pc.category_id = c.id
                WHERE p.status = 'published' AND c.slug = ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        
        $offset = ($page - 1) * $perPage;
        
        // カウント
        $countSql = "SELECT COUNT(DISTINCT p.id) as total
                     FROM posts p
                     INNER JOIN post_categories pc ON p.id = pc.post_id
                     INNER JOIN categories c ON pc.category_id = c.id
                     WHERE p.status = 'published' AND c.slug = ?";
        
        $total = $this->db->fetch($countSql, [$categorySlug])['total'];
        $totalPages = ceil($total / $perPage);
        
        $data = $this->db->fetchAll($sql, [$categorySlug, $perPage, $offset]);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }
    
    /**
     * タグ別投稿取得
     */
    public function getByTag($tagSlug, $page = 1, $perPage = 10) {
        $sql = "SELECT DISTINCT p.*
                FROM posts p
                INNER JOIN post_tags pt ON p.id = pt.post_id
                INNER JOIN tags t ON pt.tag_id = t.id
                WHERE p.status = 'published' AND t.slug = ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        
        $offset = ($page - 1) * $perPage;
        
        // カウント
        $countSql = "SELECT COUNT(DISTINCT p.id) as total
                     FROM posts p
                     INNER JOIN post_tags pt ON p.id = pt.post_id
                     INNER JOIN tags t ON pt.tag_id = t.id
                     WHERE p.status = 'published' AND t.slug = ?";
        
        $total = $this->db->fetch($countSql, [$tagSlug])['total'];
        $totalPages = ceil($total / $perPage);
        
        $data = $this->db->fetchAll($sql, [$tagSlug, $perPage, $offset]);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }
    
    /**
     * スラッグで投稿取得
     */
    public function findBySlug($slug) {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * ビュー数増加
     */
    public function incrementViewCount($id) {
        $sql = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE {$this->primaryKey} = ?";
        $this->db->query($sql, [$id]);
    }
    
    /**
     * 関連投稿取得
     */
    public function getRelated($postId, $limit = 3) {
        $sql = "SELECT DISTINCT p.*
                FROM posts p
                INNER JOIN post_categories pc ON p.id = pc.post_id
                INNER JOIN post_categories pc2 ON pc.category_id = pc2.category_id
                WHERE p.status = 'published' 
                AND p.id != ? 
                AND pc2.post_id = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$postId, $postId, $limit]);
    }
}

/**
 * カテゴリー模型
 */
class Category extends BaseModel {
    protected $table = 'categories';
    protected $fillable = ['name', 'slug', 'description', 'parent_id'];
    
    /**
     * 階層構造カテゴリー取得
     */
    public function getHierarchical() {
        $sql = "SELECT * FROM {$this->table} ORDER BY parent_id, name";
        $categories = $this->db->fetchAll($sql);
        
        return $this->buildTree($categories);
    }
    
    /**
     * ツリー構造構築
     */
    private function buildTree($categories, $parentId = null) {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $category['children'] = $this->buildTree($categories, $category['id']);
                $tree[] = $category;
            }
        }
        
        return $tree;
    }
    
    /**
     * 子カテゴリー取得
     */
    public function getChildren($parentId) {
        return $this->where(['parent_id' => $parentId], [], 'name');
    }
    
    /**
     * 親カテゴリー取得
     */
    public function getParent($categoryId) {
        $category = $this->find($categoryId);
        if ($category && $category['parent_id']) {
            return $this->find($category['parent_id']);
        }
        return null;
    }
    
    /**
     * パス取得（階層表示用）
     */
    public function getPath($categoryId) {
        $path = [];
        $current = $this->find($categoryId);
        
        while ($current) {
            array_unshift($path, $current);
            $current = $this->getParent($current['id']);
        }
        
        return $path;
    }
}

/**
 * タグ模型
 */
class Tag extends BaseModel {
    protected $table = 'tags';
    protected $fillable = ['name', 'slug'];
    
    /**
     * Popularタグ取得
     */
    public function getPopular($limit = 20) {
        $sql = "SELECT t.*, COUNT(pt.post_id) as post_count
                FROM tags t
                LEFT JOIN post_tags pt ON t.id = pt.tag_id
                LEFT JOIN posts p ON pt.post_id = p.id AND p.status = 'published'
                GROUP BY t.id
                ORDER BY post_count DESC, t.name
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * タグクラウド用データ
     */
    public function getCloudData($minPosts = 1) {
        $sql = "SELECT t.*, COUNT(pt.post_id) as post_count
                FROM tags t
                LEFT JOIN post_tags pt ON t.id = pt.tag_id
                LEFT JOIN posts p ON pt.post_id = p.id AND p.status = 'published'
                GROUP BY t.id
                HAVING post_count >= ?
                ORDER BY post_count DESC, t.name";
        
        return $this->db->fetchAll($sql, [$minPosts]);
    }
}

/**
 * ユーザー模型
 */
class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = ['username', 'email', 'display_name', 'role', 'avatar', 'bio', 'status'];
    
    /**
     * 管理者取得
     */
    public function getAdmins() {
        return $this->where(['role' => 'admin'], [], 'display_name');
    }
    
    /**
     * 著者取得
     */
    public function getAuthors() {
        return $this->where(['status' => 'active'], [], 'display_name');
    }
    
    /**
     * ユーザー名・メールでの検索
     */
    public function findByUsernameOrEmail($identifier) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ? OR email = ?";
        return $this->db->fetch($sql, [$identifier, $identifier]);
    }
    
    /**
     * パスワード検証
     */
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password_hash']);
    }
    
    /**
     * パスワード更新
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password_hash' => $hashedPassword]);
    }
    
    /**
     * 最終ログイン時間更新
     */
    public function updateLastLogin($id) {
        $sql = "UPDATE {$this->table} SET last_login = CURRENT_TIMESTAMP WHERE {$this->primaryKey} = ?";
        $this->db->query($sql, [$id]);
    }
}

/**
 * 設定模型
 */
class Setting extends BaseModel {
    protected $table = 'settings';
    protected $fillable = ['setting_key', 'setting_value', 'description'];
    
    /**
     * 設定値取得
     */
    public function get($key, $default = null) {
        $setting = $this->findBy('setting_key', $key);
        return $setting ? $setting['setting_value'] : $default;
    }
    
    /**
     * 設定値更新
     */
    public function set($key, $value, $description = null) {
        $data = [
            'setting_key' => $key,
            'setting_value' => $value
        ];
        
        if ($description !== null) {
            $data['description'] = $description;
        }
        
        // 既存の設定をチェック
        $existing = $this->findBy('setting_key', $key);
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->create($data);
        }
    }
    
    /**
     * 複数設定取得
     */
    public function getMultiple($keys) {
        $placeholders = str_repeat('?,', count($keys) - 1) . '?';
        $sql = "SELECT setting_key, setting_value FROM {$this->table} WHERE setting_key IN ({$placeholders})";
        
        $results = $this->db->fetchAll($sql, $keys);
        $settings = [];
        
        foreach ($results as $result) {
            $settings[$result['setting_key']] = $result['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * 全設定取得
     */
    public function getAll() {
        $sql = "SELECT setting_key, setting_value, description FROM {$this->table} ORDER BY setting_key";
        $results = $this->db->fetchAll($sql);
        
        $settings = [];
        foreach ($results as $result) {
            $settings[$result['setting_key']] = [
                'value' => $result['setting_value'],
                'description' => $result['description']
            ];
        }
        
        return $settings;
    }
}
?>