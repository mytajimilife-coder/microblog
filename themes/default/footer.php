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
                
                <!-- 最近のコメント -->
                <div class="widget">
                    <h3 class="widget-title">最近のコメント</h3>
                    <ul class="comment-widget-list">
                        <?php 
                            $recentComments = SidebarHelper::getRecentComments($pdo, 5);
                            if (!empty($recentComments)): 
                                foreach ($recentComments as $rc): 
                        ?>
                                    <li class="comment-widget-item">
                                        <a href="<?php echo url('?action=post&id=' . $rc['post_id']); ?>#comment-<?php echo $rc['id']; ?>">
                                            <strong><?php echo h($rc['author_name']); ?></strong> 
                                            <span style="color: #666; font-size: 13px;">on</span> 
                                            <span class="post-title-mini"><?php echo h($rc['post_title']); ?></span>
                                            <p class="comment-excerpt-mini"><?php echo JapaneseTextProcessor::truncate(strip_tags($rc['content']), 40); ?></p>
                                        </a>
                                    </li>
                        <?php 
                                endforeach;
                            else:
                        ?>
                            <p>コメントはまだありません</p>
                        <?php endif; ?>
                    </ul>
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

    <!-- Back to Top Button -->
    <button id="back-to-top" title="トップへ戻る" style="display: none; position: fixed; bottom: 30px; right: 30px; background: #3498db; color: white; border: none; padding: 12px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000; transition: opacity 0.3s, transform 0.3s;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    </button>

    <script>
    window.onscroll = function() {
        var btn = document.getElementById("back-to-top");
        if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
            btn.style.display = "block";
            btn.style.opacity = "1";
        } else {
            btn.style.opacity = "0";
            setTimeout(() => { if(btn.style.opacity === "0") btn.style.display = "none"; }, 300);
        }
    };
    document.getElementById("back-to-top").onclick = function() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    };
    </script>
</body>
</html>