<article class="post post-single">
    <?php if (isset($breadcrumb)) echo $breadcrumb; ?>
    <header class="post-header">
        <h1 class="post-title"><?php echo h($post['title']); ?></h1>
        
        <div class="post-meta">
            <span>📅 <?php echo DateTimeHelper::formatJapanese(strtotime($post['created_at'])); ?></span>
            <span> | 👁️ <?php echo number_format($post['view_count']); ?> 回表示</span>
            <span> | ✏️ 更新: <?php echo DateTimeHelper::formatJapanese(strtotime($post['updated_at'])); ?></span>
            <?php if (class_exists('UXHelper')): ?>
                <span> | <?php echo UXHelper::getReadingTimeHTML($post['content']); ?></span>
            <?php endif; ?>
        </div>

        <?php if (class_exists('SocialShare')): ?>
            <?php 
                echo SocialShare::getAssets();
                echo SocialShare::generateButtons(URLHelper::current(), $post['title'], h($post['excerpt'] ?? '')); 
            ?>
        <?php endif; ?>
        
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
                 class="featured-image"
                 loading="lazy">
        </div>
    <?php endif; ?>
    
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    
    <footer class="post-footer">
        <!-- Newsletter Subscription -->
        <div class="newsletter-section">
            <div class="newsletter-content">
                <h3>ニュースレター購読</h3>
                <p>最新記事や更新情報をメールでお届けします。</p>
                <form id="newsletter-form" class="newsletter-form">
                    <input type="email" name="email" placeholder="メールアドレスを入力" required>
                    <button type="submit">購読する</button>
                    <div id="newsletter-message" class="newsletter-message"></div>
                </form>
            </div>
        </div>

        <script>
        document.getElementById('newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const messageDiv = document.getElementById('newsletter-message');
            const email = form.email.value;
            
            form.querySelector('button').disabled = true;
            messageDiv.textContent = '送信中...';
            
            fetch('<?php echo url('subscribe'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.textContent = data.message;
                messageDiv.className = 'newsletter-message ' + (data.success ? 'success' : 'error');
                if (data.success) {
                    form.reset();
                }
            })
            .catch(error => {
                messageDiv.textContent = '通信エラーが発生しました。';
                messageDiv.className = 'newsletter-message error';
            })
            .finally(() => {
                form.querySelector('button').disabled = false;
            });
        });
        </script>

        <div class="post-navigation">
            <div class="nav-previous">
                <a href="<?php echo url(); ?>" class="nav-link">← ホームに戻る</a>
            </div>
        </div>

        <!-- Comments Section -->
        <section id="comments" class="comments-section">
            <h3 class="comments-title">
                💬 コメント (<?php echo $commentCount; ?>)
            </h3>

            <?php if (isset($_SESSION['comment_success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['comment_success']; unset($_SESSION['comment_success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['comment_error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['comment_error']; unset($_SESSION['comment_error']); ?></div>
            <?php endif; ?>

            <div class="comments-list">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" id="comment-<?php echo $comment['id']; ?>">
                            <div class="comment-avatar">
                                <div class="avatar-placeholder"><?php echo mb_substr($comment['author_name'], 0, 1); ?></div>
                            </div>
                            <div class="comment-content">
                                <div class="comment-meta">
                                    <span class="comment-author"><?php echo h($comment['author_name']); ?></span>
                                    <span class="comment-date"><?php echo DateTimeHelper::timeAgo(strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-body">
                                    <?php echo nl2br(h($comment['content'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-comments">コメントはまだありません。最初のコメントを残しませんか？</p>
                <?php endif; ?>
            </div>

            <!-- Comment Form -->
            <div class="comment-form-container">
                <h4>コメントを残す</h4>
                <form action="<?php echo url('comment'); ?>" method="POST" class="comment-form">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="comment-name">名前 <span class="required">*</span></label>
                            <input type="text" id="comment-name" name="author_name" required placeholder="名無しさん">
                        </div>
                        <div class="form-group">
                            <label for="comment-email">メール (公開されません)</label>
                            <input type="email" id="comment-email" name="author_email" placeholder="mail@example.com">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment-content">コメント <span class="required">*</span></label>
                        <textarea id="comment-content" name="content" rows="5" required placeholder="コメントを入力してください"></textarea>
                    </div>
                    <button type="submit" class="btn-submit-comment">コメントを送信</button>
                </form>
            </div>
        </section>
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

/* Newsletter Section */
.newsletter-section {
    background: #f1f8ff;
    border-radius: 12px;
    padding: 30px;
    margin: 40px 0;
    text-align: center;
    border: 1px solid #d0e7ff;
}

.newsletter-section h3 {
    margin-bottom: 10px;
    color: #0366d6;
}

.newsletter-section p {
    color: #586069;
    margin-bottom: 20px;
}

.newsletter-form {
    display: flex;
    max-width: 500px;
    margin: 0 auto;
    gap: 10px;
}

.newsletter-form input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #d1d5da;
    border-radius: 6px;
    font-size: 16px;
}

.newsletter-form button {
    background: #28a745;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
}

.newsletter-form button:hover {
    background: #218838;
}

.newsletter-message {
    margin-top: 15px;
    font-size: 14px;
}

.newsletter-message.success { color: #28a745; }
.newsletter-message.error { color: #d73a49; }

@media (max-width: 500px) {
    .newsletter-form {
        flex-direction: column;
    }
}

/* Comments Section */
.comments-section {
    margin-top: 50px;
    padding-top: 30px;
    border-top: 2px solid #ecf0f1;
}

.comments-title {
    font-size: 24px;
    margin-bottom: 30px;
    color: #2c3e50;
}

.comment-item {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.avatar-placeholder {
    width: 50px;
    height: 50px;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: bold;
    font-size: 20px;
}

.comment-content {
    flex: 1;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
}

.comment-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.comment-author {
    font-weight: bold;
    color: #2c3e50;
}

.comment-date {
    font-size: 13px;
    color: #95a5a6;
}

.comment-body {
    line-height: 1.6;
    color: #444;
}

.comment-form-container {
    margin-top: 50px;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    border: 1px solid #ecf0f1;
}

.comment-form-container h4 {
    font-size: 20px;
    margin-bottom: 20px;
    color: #2c3e50;
}

.comment-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.comment-form .form-group {
    margin-bottom: 20px;
}

.comment-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.comment-form input,
.comment-form textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 15px;
}

.btn-submit-comment {
    background: #3498db;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-submit-comment:hover {
    background: #2980b9;
}

@media (max-width: 600px) {
    .comment-form .form-row {
        grid-template-columns: 1fr;
    }
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