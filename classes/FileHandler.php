<?php
class FileHandler {
    /**
     * Upload a file to the server
     * 
     * @param array $file The $_FILES array element
     * @param string $targetDir The target directory
     * @param array $allowedTypes Array of allowed file extensions
     * @param int $maxSize Maximum file size in bytes (default: 5MB)
     * @return array Result array with success status and file info
     */
    public static function upload($file, $targetDir, $allowedTypes = [], $maxSize = 5242880) {
        try {
            // Check for errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return [
                    'success' => false,
                    'message' => self::getUploadError($file['error'])
                ];
            }
            
            // Check file size
            if ($file['size'] > $maxSize) {
                return [
                    'success' => false,
                    'message' => 'File is too large. Maximum size is ' . self::formatBytes($maxSize)
                ];
            }
            
            // Get file extension
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Check file type
            if (!empty($allowedTypes) && !in_array($fileExt, $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes)
                ];
            }
            
            // Create target directory if it doesn't exist
            if (!file_exists($targetDir)) {
                if (!mkdir($targetDir, 0755, true)) {
                    return [
                        'success' => false,
                        'message' => 'Failed to create upload directory'
                    ];
                }
            }
            
            // Generate unique filename
            $fileName = uniqid() . '.' . $fileExt;
            $targetPath = rtrim($targetDir, '/') . '/' . $fileName;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return [
                    'success' => true,
                    'file_name' => $fileName,
                    'file_path' => $targetPath,
                    'file_size' => $file['size'],
                    'file_type' => $fileExt
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to move uploaded file'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'File upload error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a file from the server
     * 
     * @param string $filePath Path to the file
     * @return bool True on success, false on failure
     */
    public static function delete($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Force download a file
     * 
     * @param string $filePath Path to the file
     * @param string $downloadName The name to use for the downloaded file
     * @return void
     */
    public static function download($filePath, $downloadName = null) {
        if (file_exists($filePath)) {
            $fileName = $downloadName ?: basename($filePath);
            $fileSize = filesize($filePath);
            $mimeType = mime_content_type($filePath);
            
            // Set headers
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . $fileSize);
            header('Pragma: public');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            
            // Clear output buffer
            ob_clean();
            flush();
            
            // Output file
            readfile($filePath);
            exit;
        }
    }
    
    /**
     * Get human-readable upload error message
     * 
     * @param int $errorCode The upload error code
     * @return string Error message
     */
    private static function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
    
    /**
     * Format bytes to human-readable format
     * 
     * @param int $bytes Size in bytes
     * @param int $precision Number of decimal places
     * @return string Formatted size
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Get file extension from MIME type
     * 
     * @param string $mime MIME type
     * @return string File extension (without dot)
     */
    public static function getExtensionFromMime($mime) {
        $mimeMap = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar'
        ];
        
        return $mimeMap[$mime] ?? 'bin';
    }
}
