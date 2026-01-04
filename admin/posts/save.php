<?php
/**
 * 投稿保存処理
 */

requirePermission('author');

try {
    // CSRFトークン検証
    $adminAuth = new AdminAuth($pdo);
    $adminAuth->validateCSRF();
    
    // リクエストデータ取得
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $categories = $_POST['categories'] ?? [];
    $tagsInput = trim($_POST['tags'] ?? '');
    
    // バリデーション
    if (empty($title)) {
        throw new Exception('タイトルを入力してください');
    }
    
    if (empty($content)) {
        throw new Exception('内容を入力してください');
    }
    
    if (!in_array($status, ['draft', 'published', 'private'])) {
        throw new Exception('無効なステータスです');
    }
    
    // スラッグ生成・検証
    if (empty($slug)) {
        $slug = URLHelper::generateSlug($title);
    }
    
    // スラッグ重複チェック
    $existingPost = $postModel->findBy('slug', $slug);
    if ($existingPost && (!$id || $existingPost['id'] != $id)) {
        throw new Exception('このスラッグは既に使用されています');
    }
    
    // 抜粋自動生成
    if (empty($excerpt)) {
        $excerpt = HTMLHelper::excerpt(strip_tags($content), 200);
    }
    
    // 現在のユーザー
    $currentUser = getCurrentUser();
    
    // アイキャッチ画像処理
    $featuredImage = null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = FileHelper::uploadFile($_FILES['featured_image'], UPLOAD_PATH);
        
        if ($uploadResult['success']) {
            $featuredImage = $uploadResult['path'];
        } else {
            throw new Exception('画像のアップロードに失敗しました: ' . $uploadResult['message']);
        }
    }
    
    // 既存投稿のアイキャッチ画像削除
    if ($id && isset($_POST['remove_featured_image']) && $_POST['remove_featured_image'] == '1') {
        $existingPost = $postModel->find($id);
        if ($existingPost && $existingPost['featured_image']) {
            FileHelper::deleteFile($existingPost['featured_image']);
        }
        $featuredImage = null;
    }
    
    // 投稿データ準備
    $postData = [
        'title' => $title,
        'content' => $content,
        'excerpt' => $excerpt,
        'slug' => $slug,
        'status' => $status,
        'author_id' => $currentUser['id']
    ];
    
    if ($featuredImage) {
        $postData['featured_image'] = $featuredImage;
    }
    
    // 投稿保存
    if ($action === 'create') {
        $postId = $postModel->create($postData);
        $message = '投稿を作成しました';
    } elseif ($action === 'edit') {
        if (!$id) {
            throw new Exception('投稿IDが無効です');
        }
        
        // 権限チェック
        $existingPost = $postModel->find($id);
        if (!$existingPost) {
            throw new Exception('投稿が見つかりません');
        }
        
        // 他人の投稿を編集できる権限チェック
        if ($existingPost['author_id'] != $currentUser['id'] && !hasPermission('editor')) {
            throw new Exception('この投稿を編集する権限がありません');
        }
        
        $postModel->update($id, $postData);
        $postId = $id;
        $message = '投稿を更新しました';
    } else {
        throw new Exception('無効なアクションです');
    }
    
    // カテゴリー関連付け処理
    // 既存の関連付け削除
    $db->query("DELETE FROM post_categories WHERE post_id = ?", [$postId]);
    
    if (!empty($categories)) {
        foreach ($categories as $categoryId) {
            $db->query("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)", 
                      [$postId, $categoryId]);
        }
    }
    
    // タグ処理
    // 既存のタグ関連付け削除
    $db->query("DELETE FROM post_tags WHERE post_id = ?", [$postId]);
    
    if (!empty($tagsInput)) {
        $tagNames = array_map('trim', explode(',', $tagsInput));
        $tagNames = array_filter($tagNames); // 空文字列除去
        
        foreach ($tagNames as $tagName) {
            // 既存タグ検索
            $existingTag = $tagModel->findBy('name', $tagName);
            
            if (!$existingTag) {
                // 新規タグ作成
                $tagSlug = URLHelper::generateSlug($tagName);
                
                // スラッグ重複チェック
                $existingTagBySlug = $tagModel->findBy('slug', $tagSlug);
                if ($existingTagBySlug) {
                    // 重複場合は連番追加
                    $counter = 1;
                    do {
                        $tagSlug = URLHelper::generateSlug($tagName) . '-' . $counter;
                        $existingTagBySlug = $tagModel->findBy('slug', $tagSlug);
                        $counter++;
                    } while ($existingTagBySlug);
                }
                
                $tagId = $tagModel->create([
                    'name' => $tagName,
                    'slug' => $tagSlug
                ]);
                
                $existingTag = $tagModel->find($tagId);
            }
            
            // タグ関連付け作成
            if ($existingTag) {
                $db->query("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?) ON CONFLICT DO NOTHING", 
                          [$postId, $existingTag['id']]);
            }
        }
    }
    
    // 成功メッセージ
    setAdminMessage($message, 'success');
    
    // リダイレクト
    if (isset($_POST['publish'])) {
        // 公開または公開更新の場合、投稿ページへ
        if ($status === 'published') {
            header('Location: ../../?action=post&id=' . $slug);
        } else {
            header('Location: index.php?action=posts');
        }
    } else {
        // 下書き保存の場合、編集ページへ
        header('Location: index.php?action=edit&id=' . $postId);
    }
    exit;
    
} catch (Exception $e) {
    setAdminMessage($e->getMessage(), 'error');
    header('Location: index.php?action=posts');
    exit;
}
?>