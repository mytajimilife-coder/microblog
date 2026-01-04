<?php
/**
 * 投稿編集ページ
 * TinyMCE エディター付き投稿作成・編集
 */

// 投稿データ取得
$post = null;
$postCategories = [];
$postTags = [];

if ($id) {
    $post = $postModel->find($id);
    if (!$post) {
        setAdminMessage('投稿が見つかりません', 'error');
        header('Location: index.php?action=posts');
        exit;
    }
    
    // 投稿のカテゴリー取得
    $postCategories = $db->fetchAll("SELECT c.* FROM categories c 
                                   INNER JOIN post_categories pc ON c.id = pc.category_id 
                                   WHERE pc.post_id = ?", [$id]);
    
    // 投稿のタグ取得
    $postTags = $db->fetchAll("SELECT t.* FROM tags t 
                              INNER JOIN post_tags pt ON t.id = pt.tag_id 
                              WHERE pt.post_id = ?", [$id]);
}

// カテゴリー・タグ全取得
$categories = $categoryModel->all('name');
$tags = $tagModel->all('name');

// 現在のユーザー
$currentUser = getCurrentUser();

// CSRFトークン
$csrfToken = SecurityHelper::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? '投稿編集' : '新規投稿'; ?> - <?php echo h(SITE_NAME); ?></title>
    
    <!-- TinyMCE エディター -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: <?php echo JAPANESE_FONT_FAMILY; ?>;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
        }
        
        .sidebar h1 {
            font-size: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            display: block;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background-color: #34495e;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h2 {
            color: #2c3e50;
        }
        
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 16px;
            font-family: inherit;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
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
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-secondary {
            background: #95a5a6;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .checkbox-item input[type="checkbox"] {
            margin: 0;
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
        
        /* TinyMCE  Styles */
        .tox-tinymce {
            border-radius: 5px !important;
        }
        
        .editor-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h1>管理パネル</h1>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="../dashboard.php" class="nav-link">ダッシュボード</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?action=posts" class="nav-link active">投稿管理</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=categories" class="nav-link">カテゴリー</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=tags" class="nav-link">タグ</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=users" class="nav-link">ユーザー</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=settings" class="nav-link">設定</a>
                    </li>
                    <li class="nav-item">
                        <a href="../../" class="nav-link" target="_blank">サイトを見る</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=logout" class="nav-link">ログアウト</a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h2><?php echo $post ? '投稿編集' : '新規投稿作成'; ?></h2>
                <div>
                    <a href="index.php?action=posts" class="btn btn-secondary">一覧に戻る</a>
                </div>
            </div>
            
            <div class="form-container">
                <form method="post" id="postForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $post ? 'edit' : 'create'; ?>">
                    <input type="hidden" name="id" value="<?php echo $post['id'] ?? ''; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="title">タイトル *</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="<?php echo h($post['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="slug">スラッグ</label>
                        <input type="text" id="slug" name="slug" class="form-input" 
                               value="<?php echo h($post['slug'] ?? ''); ?>" 
                               placeholder="URLriendly slug">
                        <small>空白の場合はタイトルから自動生成されます</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="excerpt">抜粋</label>
                        <textarea id="excerpt" name="excerpt" class="form-textarea" 
                                  placeholder="投稿の要約（オプション）"><?php echo h($post['excerpt'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">カテゴリー</label>
                        <div class="checkbox-group">
                            <?php foreach ($categories as $category): ?>
                                <?php 
                                $checked = false;
                                if ($postCategories) {
                                    foreach ($postCategories as $pc) {
                                        if ($pc['id'] == $category['id']) {
                                            $checked = true;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="cat_<?php echo $category['id']; ?>" 
                                           name="categories[]" value="<?php echo $category['id']; ?>"
                                           <?php echo $checked ? 'checked' : ''; ?>>
                                    <label for="cat_<?php echo $category['id']; ?>"><?php echo h($category['name']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="tags">タグ</label>
                        <input type="text" id="tags" name="tags" class="form-input" 
                               value="<?php 
                               if ($postTags) {
                                   echo h(implode(', ', array_column($postTags, 'name')));
                               }
                               ?>" 
                               placeholder="カンマ区切りで入力（例: タグ1, タグ2, タグ3）">
                        <small>既存のタグ可以使用するか、新しいタグが作成されます</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="status">ステータス</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="draft" <?php echo ($post['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>下書き</option>
                            <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>公開</option>
                            <option value="private" <?php echo ($post['status'] ?? '') === 'private' ? 'selected' : ''; ?>>非公開</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="featured_image">アイキャッチ画像</label>
                        <input type="file" id="featured_image" name="featured_image" class="form-input" 
                               accept="image/*">
                        <?php if ($post['featured_image']): ?>
                            <div style="margin-top: 10px;">
                                <img src="../../<?php echo h($post['featured_image']); ?>" 
                                     alt="アイキャッチ画像" style="max-width: 200px; height: auto;">
                                <br>
                                <label>
                                    <input type="checkbox" name="remove_featured_image" value="1">
                                    画像を削除
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="editor-container">
                        <label class="form-label">内容 *</label>
                        <textarea id="content" name="content"><?php echo $post['content'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php?action=posts" class="btn btn-secondary">キャンセル</a>
                        <button type="submit" name="save_draft" class="btn">下書き保存</button>
                        <button type="submit" name="publish" class="btn btn-success"><?php echo $post ? '更新' : '公開'; ?></button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        // TinyMCE 初期化（日本語対応）
        tinymce.init({
            selector: '#content',
            height: 500,
            language: 'ja',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount', 'paste'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic forecolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | image media link | code preview | help',
            content_style: `
                body { 
                    font-family: 'Hiragino Sans', 'Yu Gothic', 'Meiryo', sans-serif; 
                    font-size: 16px; 
                    line-height: 1.6;
                }
                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    color: #999;
                    font-style: normal;
                }
            `,
            // 日本語フォント設定
            font_family_formats: `
                Hiragino Sans=Hiragino Sans, sans-serif;
                Yu Gothic=Yu Gothic, sans-serif;
                Meiryo=Meiryo, sans-serif;
                Arial=arial,helvetica,sans-serif;
                Helvetica=helvetica,sans-serif;
                Times New Roman=times new roman,times,serif;
                Georgia=georgia,times,serif;
                Courier New=courier new,courier,monospace;
            `,
            // 日本語フォントサイズ設定
            font_size_formats: '12px 14px 16px 18px 20px 24px 28px 32px 36px 48px',
            // 文字数制限
            setup: function (editor) {
                editor.on('init', function () {
                    editor.getContainer().style.fontFamily = 'Hiragino Sans, Yu Gothic, Meiryo, sans-serif';
                });
            },
            // ファイルアップロード設定
            images_upload_handler: function (blobInfo, progress) {
                return new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    
                    fetch('../../upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            resolve(result.url);
                        } else {
                            reject('アップロード失敗: ' + result.message);
                        }
                    })
                    .catch(error => {
                        reject('アップロードエラー: ' + error);
                    });
                });
            },
            // ファイルタイプ制限
            automatic_uploads: true,
            images_upload_types: 'jpg,jpeg,png,gif,webp',
            images_upload_max_size: 5242880 // 5MB
        });
        
        // タイトルからスラッグ自動生成
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slugField = document.getElementById('slug');
            
            if (!slugField.value || slugField.dataset.autoGenerated === 'true') {
                const slug = generateSlug(title);
                slugField.value = slug;
                slugField.dataset.autoGenerated = 'true';
            }
        });
        
        // スラッグ手動編集時の処理
        document.getElementById('slug').addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
        
        // スラッグ生成関数
        function generateSlug(text) {
            return text
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '') // 特殊文字除去
                .replace(/[\s_-]+/g, '-') // 空白・ハイフン統一
                .replace(/^-+|-+$/g, ''); // 前後ハイフン除去
        }
        
        // フォーム送信前バリデーション
        document.getElementById('postForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = tinymce.get('content').getContent();
            
            if (!title) {
                e.preventDefault();
                alert('タイトルを入力してください');
                return false;
            }
            
            if (!content || content === '<p></p>' || content === '') {
                e.preventDefault();
                alert('内容を入力してください');
                return false;
            }
            
            // 下書き保存または公開ボタンの判定
            const publishButton = e.submitter;
            if (publishButton && publishButton.name === 'publish') {
                document.getElementById('status').value = 'published';
            }
        });
    </script>
</body>
</html>