<?php
require_once __DIR__ . '/includes/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controller
require_once __DIR__ . '/controllers/DigitalAssetController.php';
$controller = new DigitalAssetController();

// Handle actions
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Route requests
switch ($action) {
    case 'upload':
        $controller->upload();
        break;
        
    case 'download':
        if ($id) {
            $controller->download($id);
        } else {
            Utils::redirect('digital-assets.php');
        }
        break;
        
    case 'delete':
        if ($id) {
            $controller->delete($id);
        } else {
            Utils::redirect('digital-assets.php');
        }
        break;
        
    case 'index':
    default:
        $controller->index();
        break;
}
