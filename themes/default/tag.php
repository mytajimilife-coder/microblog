<div class="tag-header">
    <h1>#<?php echo h($tag['name']); ?></h1>
    <p class="tag-description">「<?php echo h($tag['name']); ?>」タグが付いた投稿一覧</p>
</div>

<?php if (empty($posts)): ?>
    <div class="no-posts">
        <h2>このタグが付いた投稿はまだありません</h2>
        <p>しばらくお待ちください。很快就会有新内容。</p>
    </div>
<?php else: ?>
    <div class="posts-list">
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
                    </div>
                </header>
                
                <?php if ($post['featured_image']): ?>
                    <div class="featured-image-container">
                        <a href="<?php echo url('post/' . $post['slug']); ?>">
                            <img src="<?php echo h($post['featured_image']); ?>" 
                                 alt="<?php echo h($post['title']); ?>" 
                                 class="featured-image">
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
                </footer>
            </article>
        <?php endforeach; ?>
    </div>
    
    <?php if (!empty($pagination)): ?>
        <div class="pagination">
            <?php echo $pagination; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($relatedTags)): ?>
    <section class="related-tags">
        <h3>関連するタグ</h3>
        <div class="tag-cloud">
            <?php foreach ($relatedTags as $relatedTag): ?>
                <a href="<?php echo url('tag/' . $relatedTag['slug']); ?>" class="tag">
                    #<?php echo h($relatedTag['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<style>
.tag-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #ecf0f1;
    text-align: center;
}

.tag-header h1 {
    font-size: 32px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.tag-description {
    font-size: 16px;
    color: #666;
}

.posts-list {
    margin-bottom: 40px;
}

.related-tags {
    margin-top: 50px;
    padding-top: 30px;
    border-top: 2px solid #ecf0f1;
    text-align: center;
}

.related-tags h3 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #2c3e50;
}

.related-tags .tag-cloud {
    justify-content: center;
}
</style>