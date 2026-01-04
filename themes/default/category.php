<div class="category-header">
    <h1><?php echo h($category['name']); ?></h1>
    
    <?php if (!empty($category['description'])): ?>
        <p class="category-description"><?php echo h($category['description']); ?></p>
    <?php endif; ?>
    
    <?php if (!empty($categoryPath)): ?>
        <nav class="breadcrumb">
            <a href="<?php echo url(); ?>">ホーム</a>
            <?php foreach ($categoryPath as $pathItem): ?>
                <?php if ($pathItem['id'] !== $category['id']): ?>
                    <span> › </span>
                    <a href="<?php echo url('category/' . $pathItem['slug']); ?>"><?php echo h($pathItem['name']); ?></a>
                <?php else: ?>
                    <span> › </span>
                    <strong><?php echo h($pathItem['name']); ?></strong>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</div>

<?php if (empty($posts)): ?>
    <div class="no-posts">
        <h2>このカテゴリーにはまだ投稿がありません</h2>
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

<?php if (!empty($childCategories)): ?>
    <section class="subcategories">
        <h3>サブカテゴリー</h3>
        <div class="subcategories-grid">
            <?php foreach ($childCategories as $childCategory): ?>
                <div class="subcategory-card">
                    <h4>
                        <a href="<?php echo url('category/' . $childCategory['slug']); ?>">
                            <?php echo h($childCategory['name']); ?>
                        </a>
                    </h4>
                    <?php if (!empty($childCategory['description'])): ?>
                        <p class="subcategory-description"><?php echo h($childCategory['description']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<style>
.category-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #ecf0f1;
}

.category-header h1 {
    font-size: 32px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.category-description {
    font-size: 16px;
    color: #666;
    margin-bottom: 15px;
}

.breadcrumb {
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb strong {
    color: #2c3e50;
}

.posts-list {
    margin-bottom: 40px;
}

.subcategories {
    margin-top: 50px;
    padding-top: 30px;
    border-top: 2px solid #ecf0f1;
}

.subcategories h3 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #2c3e50;
}

.subcategories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.subcategory-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.subcategory-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.subcategory-card h4 {
    margin-bottom: 10px;
}

.subcategory-card h4 a {
    color: #2c3e50;
    text-decoration: none;
    font-size: 18px;
}

.subcategory-card h4 a:hover {
    color: #3498db;
}

.subcategory-description {
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}
</style>