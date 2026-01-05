<?php
/**
 * お問い合わせコントローラー
 */

class ContactController {
    private $db;
    private $contactSystem;
    private $settingModel;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->contactSystem = new ContactSystem($pdo);
        $this->settingModel = new Setting($pdo);
    }
    
    /**
     * お問い合わせページ表示
     */
    public function index() {
        // 設定取得
        $settings = $this->settingModel->getMultiple(['site_name', 'site_description']);
        $siteInfo = [
            'name' => $settings['site_name'] ?? SITE_NAME,
            'description' => $settings['site_description'] ?? SITE_DESCRIPTION
        ];
        
        $seoTitle = 'お問い合わせ' . ' | ' . $siteInfo['name'];
        $seoDescription = $siteInfo['description'];
        
        $this->render('contact', [
            'siteInfo' => $siteInfo,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'title' => 'お問い合わせ'
        ]);
    }
    
    /**
     * お問い合わせ送信処理
     */
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('contact'));
            exit;
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'message' => $_POST['message'] ?? ''
        ];
        
        $result = $this->contactSystem->submitMessage($data);
        
        if ($result['success']) {
            $_SESSION['contact_success'] = $result['message'];
        } else {
            $_SESSION['contact_error'] = $result['message'];
        }
        
        header('Location: ' . url('contact'));
        exit;
    }
    
    /**
     * ビュー描画
     */
    private function render($template, $data = []) {
        extract($data);
        include 'themes/default/header.php';
        include "themes/default/{$template}.php";
        include 'themes/default/footer.php';
    }
}
