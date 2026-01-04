<article class="post post-single">
    <header class="post-header">
        <h1 class="post-title"><?php echo h($post['title']); ?></h1>
        
        <div class="post-meta">
            <span>📅 <?php echo DateTimeHelper::formatJapanese(strtotime($post['created_at'])); ?></span>
            <span> | 👁️ <?php echo number_format($post['view_count']); ?> 回表示</span>
            <span> | ✏️ 更新: <?php echo DateTimeHelper::formatJapanese(strtotime($post['updated_at'])); ?></span>
        </div>
        
        <?php if (!empty($categories) || !empty($tags)): ?>
            <div class="post-taxonomies">
                <?php if (!empty($categories)): ?>
                    <div class="post-categories">
                        <strong>カテゴリー:</strong>
                        <?php foreach ($categories as $index => $category): ?>
                            <?php if ($index > 0) echo ', '; ?>
                            <a href="<?php echo url('category/' . $category['slug']); ?>" class="category-link">
                                <?php echo h($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($tags)): ?>
                    <div class="post-tags">
                        <strong>タグ:</strong>
                        <?php foreach ($tags as $index => $tag): ?>
                            <?php if ($index > 0) echo ' '; ?>
                            <a href="<?php echo url('tag/' . $tag['slug']); ?>" class="tag-link">
                                #<?php echo h($tag['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </header>
    
    <?php if ($post['featured_image']): ?>
        <div class="featured-image-container">
            <img src="<?php echo h($post['featured_image']); ?>" 
                 alt="<?php echo h($post['title']); ?>" 
                 class="featured-image">
        </div>
    <?php endif; ?>
    
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    
    <footer class="post-footer">
        <div class="post-navigation">
            <div class="nav-previous">
                <a href="<?php echo url(); ?>" class="nav-link">← ホームに戻る</a>
            </div>
        </div>
    </footer>
</article>

<?php if (!empty($relatedPosts)): ?>
    <section class="related-posts">
        <h3>関連投稿</h3>
        <div class="related-posts-grid">
            <?php foreach ($relatedPosts as $relatedPost): ?>
                <article class="related-post">
                    <h4>
                        <a href="<?php echo url('post/' . $relatedPost['slug']); ?>">
                            <?php echo h($relatedPost['title']); ?>
                        </a>
                    </h4>
                    <div class="related-post-meta">
                        <?php echo DateTimeHelper::timeAgo(strtotime($relatedPost['created_at'])); ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<style>
.post-single {
    max-width: 800px;
    margin: 0 auto;
}

.post-header {
    margin-bottom: 30px;
    text-align: center;
}

.post-title {
    font-size: 36px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #2c3e50;
    line-height: 1.3;
}

.post-meta {
    font-size: 14px;
    color: #666;
    margin-bottom: 20px;
}

.post-taxonomies {
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.post-categories,
.post-tags {
    margin-bottom: 10px;
}

.post-categories:last-child,
.post-tags:last-child {
    margin-bottom: 0;
}

.category-link,
.tag-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
}

.category-link:hover,
.tag-link:hover {
    text-decoration: underline;
}

.tag-link {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: normal;
}

.featured-image-container {
    margin: 30px 0;
    text-align: center;
}

.featured-image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.post-content {
    margin: 30px 0;
    font-size: 16px;
    line-height: 1.8;
}

.post-content h1,
.post-content h2,
.post-content h3,
.post-content h4,
.post-content h5,
.post-content h6 {
    margin-top: 30px;
    margin-bottom: 15px;
    color: #2c3e50;
}

.post-content h1 { font-size: 32px; }
.post-content h2 { font-size: 28px; }
.post-content h3 { font-size: 24px; }
.post-content h4 { font-size: 20px; }

.post-content p {
    margin-bottom: 20px;
}

.post-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 20px 0;
}

.post-content blockquote {
    background: #f8f9fa;
    border-left: 4px solid #3498db;
    padding: 20px;
    margin: 25px 0;
    font-style: italic;
    border-radius: 0 8px 8px 0;
}

.post-content code {
    background: #f1f2f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 14px;
}

.post-content pre {
    background: #2c3e50;
    color: white;
    padding: 20px;
    border-radius: 8px;
    overflow-x: auto;
    margin: 20px 0;
}

.post-content pre code {
    background: none;
    padding: 0;
    color: inherit;
}

.post-content ul,
.post-content ol {
    margin: 20px 0;
    padding-left: 30px;
}

.post-content li {
    margin-bottom: 8px;
}

.post-footer {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #ecf0f1;
}

.post-navigation {
    text-align: center;
}

.nav-link {
    display: inline-block;
    background: #3498db;
    color: white;
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.nav-link:hover {
    background: #2980b9;
}

.related-posts {
    margin-top: 50px;
    padding-top: 30px;
    border-top: 2px solid #ecf0f1;
}

.related-posts h3 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #2c3e50;
    text-align: center;
}

.related-posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.related-post {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.related-post:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.related-post h4 {
    margin-bottom: 10px;
}

.related-post h4 a {
    color: #2c3e50;
    text-decoration: none;
    font-size: 16px;
}

.related-post h4 a:hover {
    color: #3498db;
}

.related-post-meta {
    font-size: 12px;
    color: #666;
}

/* Responsive */
@media (max-width: 768px) {
    .post-title {
        font-size: 28px;
    }
    
    .related-posts-grid {
        grid-template-columns: 1fr;
    }
    
    .post-content {
        font-size: 15px;
    }
}
</style>