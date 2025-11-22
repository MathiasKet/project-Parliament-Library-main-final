<?php
require_once __DIR__ . '/../classes/DigitalAsset.php';
require_once __DIR__ . '/../classes/FileHandler.php';
require_once __DIR__ . '/../classes/Utils.php';

class DigitalAssetController {
    private $asset;
    private $fileHandler;
    
    public function __construct() {
        $this->asset = new DigitalAsset();
        $this->fileHandler = new FileHandler();
        $this->checkAuth();
    }
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            Utils::redirect('login.php');
        }
    }
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = $_GET['search'] ?? '';
        $categoryId = $_GET['category_id'] ?? '';
        
        $filters = [
            'search' => $search,
            'category_id' => $categoryId,
            'admin_view' => $this->isAdmin()
        ];
        
        $itemsPerPage = 12;
        $offset = ($page - 1) * $itemsPerPage;
        
        $assets = $this->asset->getAssets($filters, $itemsPerPage, $offset);
        $totalItems = $this->asset->countAssets($filters);
        
        $categories = $this->asset->getCategories();
        
        // Create paginator
        $paginator = new Paginator($totalItems, $itemsPerPage, $page, '?page={page}&search=' . urlencode($search) . '&category_id=' . urlencode($categoryId));
        
        include __DIR__ . '/../views/digital-assets/index.php';
    }
    
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_FILES['file'])) {
                $_SESSION['error'] = 'Please select a file to upload';
                Utils::redirect('digital-assets.php?action=upload');
            }
            
            $file = $_FILES['file'];
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
                'is_public' => isset($_POST['is_public']) ? 1 : 0
            ];
            
            $result = $this->asset->uploadAsset($file, $data, $_SESSION['user_id']);
            
            if ($result['success']) {
                $_SESSION['success'] = 'File uploaded successfully';
                Utils::redirect('digital-assets.php');
            } else {
                $_SESSION['error'] = $result['message'];
                Utils::redirect('digital-assets.php?action=upload');
            }
        }
        
        $categories = $this->asset->getCategories();
        include __DIR__ . '/../views/digital-assets/upload.php';
    }
    
    public function download($id) {
        $asset = $this->asset->getAsset($id);
        
        if (!$asset) {
            $_SESSION['error'] = 'File not found';
            Utils::redirect('digital-assets.php');
        }
        
        // Check if user has permission to download
        if (!$asset->is_public && $asset->uploaded_by != $_SESSION['user_id'] && !$this->isAdmin()) {
            $_SESSION['error'] = 'You do not have permission to download this file';
            Utils::redirect('digital-assets.php');
        }
        
        $filePath = $asset->file_path;
        $fileName = $asset->title . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
        
        // Record download in database
        $this->asset->recordDownload($id, $_SESSION['user_id']);
        
        // Send file for download
        $this->fileHandler->download($filePath, $fileName);
    }
    
    public function delete($id) {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = 'You do not have permission to delete files';
            Utils::redirect('digital-assets.php');
        }
        
        if ($this->asset->deleteAsset($id)) {
            $_SESSION['success'] = 'File deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete file';
        }
        
        Utils::redirect('digital-assets.php');
    }
    
    private function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
