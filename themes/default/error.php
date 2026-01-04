<div class="error-page">
    <div class="error-icon">⚠️</div>
    <h1 class="error-title">ページが見つかりません</h1>
    <p class="error-message"><?php echo h($message ?? 'ページが見つかりません'); ?></p>
    <div class="error-actions">
        <a href="<?php echo url(); ?>" class="back-home">ホームに戻る</a>
        <a href="<?php echo url('admin'); ?>" class="admin-link">管理パネル</a>
    </div>
</div>

<style>
.error-page {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin: 40px auto;
    max-width: 600px;
}

.error-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.error-title {
    font-size: 36px;
    color: #e74c3c;
    margin-bottom: 15px;
    font-weight: bold;
}

.error-message {
    font-size: 18px;
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

.error-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.back-home,
.admin-link {
    display: inline-block;
    padding: 12px 24px;
    background: #3498db;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
    font-weight: 500;
}

.back-home:hover {
    background: #2980b9;
}

.admin-link {
    background: #95a5a6;
}

.admin-link:hover {
    background: #7f8c8d;
}
</style>