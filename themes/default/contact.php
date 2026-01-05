<div class="contact-page">
    <h1><?php echo h($title); ?></h1>
    
    <div class="contact-intro">
        <p>ご意見、ご要望、お問い合わせなどがございましたら、以下のフォームよりお気軽にご連絡ください。</p>
    </div>

    <?php if (isset($_SESSION['contact_success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['contact_success']; unset($_SESSION['contact_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['contact_error'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['contact_error']; unset($_SESSION['contact_error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo url('contact/submit'); ?>" method="POST" class="contact-form">
        <div class="form-group">
            <label for="name">お名前 <span class="required">*</span></label>
            <input type="text" id="name" name="name" required placeholder="山田 太郎">
        </div>

        <div class="form-group">
            <label for="email">メールアドレス <span class="required">*</span></label>
            <input type="email" id="email" name="email" required placeholder="example@mail.com">
        </div>

        <div class="form-group">
            <label for="subject">件名</label>
            <input type="text" id="subject" name="subject" placeholder="お問い合わせ内容の要約">
        </div>

        <div class="form-group">
            <label for="message">メッセージ内容 <span class="required">*</span></label>
            <textarea id="message" name="message" rows="8" required placeholder="お問い合わせ内容をご記入ください"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">送信する</button>
        </div>
    </form>
</div>

<style>
.contact-page {
    max-width: 700px;
    margin: 0 auto;
}

.contact-page h1 {
    font-size: 32px;
    margin-bottom: 20px;
    text-align: center;
}

.contact-intro {
    margin-bottom: 30px;
    color: #666;
    text-align: center;
}

.contact-form {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3498db;
}

.required {
    color: #e74c3c;
}

.btn-submit {
    display: block;
    width: 100%;
    background: #3498db;
    color: white;
    border: none;
    padding: 15px;
    border-radius: 8px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-submit:hover {
    background: #2980b9;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>
