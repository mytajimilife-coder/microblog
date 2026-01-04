<?php
/**
 * 画像処理クラス
 * 画像のリサイズ、最適化、サムネイル生成
 */

class ImageProcessor {
    private $maxWidth = 1920;
    private $maxHeight = 1080;
    private $thumbnailWidth = 300;
    private $thumbnailHeight = 200;
    private $quality = 85;
    
    /**
     * 画像アップロードと最適化
     */
    public function uploadAndOptimize($file, $destination = null) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'ファイルアップロードエラー'];
        }
        
        // ファイルサイズチェック
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'ファイルサイズが大きすぎます'];
        }
        
        // 画像タイプチェック
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'message' => '有効な画像ファイルではありません'];
        }
        
        $mimeType = $imageInfo['mime'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'message' => '許可されていない画像形式です'];
        }
        
        // ファイル名生成
        $extension = $this->getExtensionFromMime($mimeType);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // アップロード先
        if ($destination === null) {
            $destination = UPLOAD_PATH;
        }
        
        // ディレクトリ作成
        if (!is_dir($destination)) {
            @mkdir($destination, 0755, true);
        }
        
        $uploadPath = $destination . $filename;
        
        // 画像を読み込み
        $sourceImage = $this->loadImage($file['tmp_name'], $mimeType);
        if ($sourceImage === false) {
            return ['success' => false, 'message' => '画像の読み込みに失敗しました'];
        }
        
        // リサイズ
        $resizedImage = $this->resizeImage($sourceImage, $this->maxWidth, $this->maxHeight);
        
        // 保存
        $saved = $this->saveImage($resizedImage, $uploadPath, $mimeType);
        
        // メモリ解放
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        
        if (!$saved) {
            return ['success' => false, 'message' => '画像の保存に失敗しました'];
        }
        
        // サムネイル生成
        $thumbnailPath = $this->generateThumbnail($uploadPath, $destination, $mimeType);
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $uploadPath,
            'thumbnail' => $thumbnailPath,
            'url' => url($uploadPath),
            'thumbnail_url' => url($thumbnailPath)
        ];
    }
    
    /**
     * 画像読み込み
     */
    private function loadImage($path, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            case 'image/webp':
                return imagecreatefromwebp($path);
            default:
                return false;
        }
    }
    
    /**
     * 画像リサイズ
     */
    private function resizeImage($sourceImage, $maxWidth, $maxHeight) {
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        
        // リサイズが必要かチェック
        if ($sourceWidth <= $maxWidth && $sourceHeight <= $maxHeight) {
            // リサイズ不要、元の画像を返す
            $newImage = imagecreatetruecolor($sourceWidth, $sourceHeight);
            imagecopy($newImage, $sourceImage, 0, 0, 0, 0, $sourceWidth, $sourceHeight);
            return $newImage;
        }
        
        // アスペクト比を保持してリサイズ
        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $newWidth = (int)($sourceWidth * $ratio);
        $newHeight = (int)($sourceHeight * $ratio);
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // 透明度を保持（PNG/GIF用）
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        imagecopyresampled(
            $newImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $sourceWidth, $sourceHeight
        );
        
        return $newImage;
    }
    
    /**
     * 画像保存
     */
    private function saveImage($image, $path, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagejpeg($image, $path, $this->quality);
            case 'image/png':
                return imagepng($image, $path, 9);
            case 'image/gif':
                return imagegif($image, $path);
            case 'image/webp':
                return imagewebp($image, $path, $this->quality);
            default:
                return false;
        }
    }
    
    /**
     * サムネイル生成
     */
    public function generateThumbnail($sourcePath, $destination, $mimeType = null) {
        if ($mimeType === null) {
            $imageInfo = getimagesize($sourcePath);
            $mimeType = $imageInfo['mime'];
        }
        
        $sourceImage = $this->loadImage($sourcePath, $mimeType);
        if ($sourceImage === false) {
            return false;
        }
        
        $thumbnail = $this->resizeImage($sourceImage, $this->thumbnailWidth, $this->thumbnailHeight);
        
        $pathInfo = pathinfo($sourcePath);
        $thumbnailFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        $thumbnailPath = $destination . $thumbnailFilename;
        
        $saved = $this->saveImage($thumbnail, $thumbnailPath, $mimeType);
        
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
        
        return $saved ? $thumbnailPath : false;
    }
    
    /**
     * MIMEタイプから拡張子取得
     */
    private function getExtensionFromMime($mimeType) {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        
        return $extensions[$mimeType] ?? 'jpg';
    }
    
    /**
     * 画像削除（サムネイルも含む）
     */
    public function deleteImage($filepath) {
        $deleted = false;
        
        // メイン画像削除
        if (file_exists($filepath) && is_file($filepath)) {
            $deleted = @unlink($filepath);
        }
        
        // サムネイル削除
        $pathInfo = pathinfo($filepath);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        if (file_exists($thumbnailPath) && is_file($thumbnailPath)) {
            @unlink($thumbnailPath);
        }
        
        return $deleted;
    }
}
?>
