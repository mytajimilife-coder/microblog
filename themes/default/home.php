<?php if (empty($posts)): ?>
    <div class="no-posts">
        <h2>投稿はまだありません</h2>
        <p>しばらくお待ちください。很快就会有新内容。</p>
    </div>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article class="post">
            <header>
                <h2 class="post-title">
                    <a href="<?php echo url('post/' . $post['slug']); ?>">
                        <?php echo h($post['title']); ?>
                    </a>
                </h2>
                
                <div class="post-meta">
                    <span>📅 <?php echo DateTimeHelper::formatJapanese(strtotime($post['created_at'])); ?></span>
                    <span> | 👁️ <?php echo number_format($post['view_count']); ?> 回表示</span>
                    
                    <?php if (!empty($post['categories'])): ?>
                        <span> | 📁 
                            <?php foreach ($post['categories'] as $index => $category): ?>
                                <?php if ($index > 0) echo ', '; ?>
                                <a href="<?php echo url('category/' . $category['slug']); ?>" style="color: #3498db; text-decoration: none;">
                                    <?php echo h($category['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>
            
            <?php if ($post['featured_image']): ?>
                <div class="featured-image-container">
                    <a href="<?php echo url('post/' . $post['slug']); ?>">
                        <img src="<?php echo h($post['featured_image']); ?>" 
                             alt="<?php echo h($post['title']); ?>" 
                             class="featured-image"
                             loading="lazy">
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="post-excerpt">
                <?php echo $post['excerpt'] ? HTMLHelper::escape($post['excerpt']) : HTMLHelper::excerpt($post['content'], 200); ?>
            </div>
            
            <footer>
                <a href="<?php echo url('post/' . $post['slug']); ?>" class="read-more">
                    続きを読み →
                </a>
                
                <?php if (!empty($post['tags'])): ?>
                    <div class="post-tags" style="margin-top: 15px;">
                        <?php foreach ($post['tags'] as $tag): ?>
                            <a href="<?php echo url('tag/' . $tag['slug']); ?>" class="tag" style="margin-right: 8px;">
                                #<?php echo h($tag['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </footer>
        </article>
    <?php endforeach; ?>
    
    <?php if (!empty($pagination)): ?>
        <div class="pagination">
            <?php echo $pagination; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<style>
.no-posts {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-posts h2 {
    font-size: 24px;
    margin-bottom: 15px;
    color: #2c3e50;
}

.featured-image-container {
    margin: 20px 0;
    overflow: hidden;
    border-radius: 8px;
}

.post-tags {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ecf0f1;
}

.post-tags .tag {
    font-size: 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    color: #495057;
    padding: 3px 8px;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s;
}

.post-tags .tag:hover {
    background: #e9ecef;
    color: #212529;
}
</style>