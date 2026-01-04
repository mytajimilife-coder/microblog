            </main>
            
            <aside class="sidebar">
                <!-- 人気タグ -->
                <div class="widget">
                    <h3 class="widget-title">人気のタグ</h3>
                    <div class="tag-cloud">
                        <?php if (!empty($popularTags)): ?>
                            <?php foreach ($popularTags as $tag): ?>
                                <a href="<?php echo url('tag/' . $tag['slug']); ?>" class="tag">
                                    <?php echo h($tag['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>タグはまだありません</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- カテゴリー -->
                <div class="widget">
                    <h3 class="widget-title">カテゴリー</h3>
                    <ul class="category-list">
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <li class="category-item">
                                    <a href="<?php echo url('category/' . $category['slug']); ?>">
                                        <span><?php echo h($category['name']); ?></span>
                                        <span style="color: #666; font-size: 12px;">
                                            <?php echo $category['post_count'] ?? 0; ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="category-item">
                                <span style="color: #666;">カテゴリーはまだありません</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- サイト情報 -->
                <div class="widget">
                    <h3 class="widget-title">このサイトについて</h3>
                    <p><?php echo h($siteInfo['description']); ?></p>
                    <p style="margin-top: 15px;">
                        <a href="<?php echo url('admin'); ?>" style="color: #3498db; text-decoration: none;">管理パネル</a>
                    </p>
                </div>
            </aside>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo h($siteInfo['name']); ?>. All rights reserved.</p>
            <p style="margin-top: 10px; opacity: 0.8;">
                Powered by <a href="#" style="color: white; text-decoration: none;">Microblog</a> - 
                日本語専用の高機能ブログシステム
            </p>
        </div>
    </footer>
</body>
</html>