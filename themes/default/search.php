<!DOCTYPE html>
<div class="search-results">
    <div class="search-header">
        <h1>検索結果: "<?php echo h($query); ?>"</h1>
        <p class="search-count"><?php echo $total; ?> 件の記事が見つかりました</p>
    </div>
    
    <?php if (!empty($posts)): ?>
        <div class="posts-list">
            <?php foreach ($posts as $post): ?>
                <article class="post-item">
                    <h2 class="post-title">
                        <a href="<?php echo url('?action=post&id=' . $post['id']); ?>">
                            <?php 
                                $title = h($post['title']);
                                echo UXHelper::highlightSearchTerms($title, $query); 
                            ?>
                        </a>
                    </h2>
                    
                    <div class="post-meta">
                        <span class="post-date">
                            <?php echo date('Y年m月d日', strtotime($post['created_at'])); ?>
                        </span>
                        <span class="post-views">
                            閲覧数: <?php echo $post['view_count']; ?>
                        </span>
                    </div>
                    
                    <div class="post-excerpt">
                        <?php 
                        $excerpt = $post['excerpt'] ?: HTMLHelper::excerpt($post['content'], 200);
                        $excerpt = h($excerpt);
                        echo UXHelper::highlightSearchTerms($excerpt, $query); 
                        ?>
                    </div>
                    
                    <a href="<?php echo url('?action=post&id=' . $post['id']); ?>" class="read-more">
                        続きを読む →
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php echo HTMLHelper::pagination($page, $totalPages, '', ['action' => 'search', 'q' => $query]); ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-results">
            <p>検索結果が見つかりませんでした。</p>
            <p>別のキーワードで検索してみてください。</p>
        </div>
    <?php endif; ?>
</div>

<style>
.search-results {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.search-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e5e9;
}

.search-header h1 {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.search-count {
    color: #666;
    font-size: 14px;
}

.posts-list {
    margin-bottom: 30px;
}

.post-item {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e1e5e9;
}

.post-item:last-child {
    border-bottom: none;
}

.post-title {
    font-size: 24px;
    margin-bottom: 10px;
}

.post-title a {
    color: #2c3e50;
    text-decoration: none;
    transition: color 0.3s;
}

.post-title a:hover {
    color: #3498db;
}

.post-meta {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
}

.post-meta span {
    margin-right: 15px;
}

.post-excerpt {
    color: #555;
    line-height: 1.6;
    margin-bottom: 15px;
}

.read-more {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
}

.read-more:hover {
    color: #2980b9;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-results p {
    font-size: 18px;
    margin-bottom: 10px;
}

.search-highlight {
    background-color: #fff3cd;
    color: #856404;
    padding: 0 2px;
    font-weight: bold;
    border-radius: 2px;
}
</style>
