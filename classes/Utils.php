<?php
class Utils {
    /**
     * Sanitize input data
     * 
     * @param mixed $data The input data to sanitize
     * @return mixed Sanitized data
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $data;
    }
    
    /**
     * Validate email address
     * 
     * @param string $email Email address to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate URL
     * 
     * @param string $url URL to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Format date
     * 
     * @param string $date Date string or timestamp
     * @param string $format Output format (default: 'd M Y')
     * @return string Formatted date
     */
    public static function formatDate($date, $format = 'd M Y') {
        if (empty($date)) {
            return '';
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }
    
    /**
     * Format date and time
     * 
     * @param string $date Date string or timestamp
     * @param string $format Output format (default: 'd M Y H:i')
     * @return string Formatted date and time
     */
    public static function formatDateTime($date, $format = 'd M Y H:i') {
        return self::formatDate($date, $format);
    }
    
    /**
     * Generate a random string
     * 
     * @param int $length Length of the random string
     * @return string Random string
     */
    public static function randomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Generate a secure random token
     * 
     * @param int $length Length of the token
     * @return string Random token
     */
    public static function generateToken($length = 32) {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        } else {
            // Fallback to less secure method if no better option is available
            return self::randomString($length * 2);
        }
    }
    
    /**
     * Redirect to another page
     * 
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code (default: 302)
     */
    public static function redirect($url, $statusCode = 302) {
        if (!headers_sent()) {
            header('Location: ' . $url, true, $statusCode);
        } else {
            echo "<script>window.location.href='$url';</script>";
        }
        exit();
    }
    
    /**
     * Check if the request is AJAX
     * 
     * @return bool True if AJAX request, false otherwise
     */
    public static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Send JSON response
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code (default: 200)
     */
    public static function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    public static function getClientIp() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Handle multiple IPs in X-Forwarded-For
        $ips = explode(',', $ip);
        return trim($ips[0]);
    }
    
    /**
     * Truncate text to a specified length
     * 
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @param string $suffix Suffix to add if text is truncated
     * @return string Truncated text
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        $truncated = mb_substr($text, 0, $length - mb_strlen($suffix));
        return $truncated . $suffix;
    }
    
    /**
     * Convert array to CSV and force download
     * 
     * @param array $data Array of data
     * @param string $filename Output filename
     * @param string $delimiter CSV delimiter
     */
    public static function arrayToCsvDownload($data, $filename = 'export.csv', $delimiter = ',') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fputs($output, "\xEF\xBB\xBF");
        
        // Add headers if this is the first row
        if (!empty($data[0]) && is_array($data[0])) {
            fputcsv($output, array_keys($data[0]), $delimiter);
        }
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row, $delimiter);
        }
        
        fclose($output);
        exit();
    }
    
    /**
     * Generate a slug from a string
     * 
     * @param string $text Text to convert to slug
     * @return string Slug
     */
    public static function slugify($text) {
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim
        $text = trim($text, '-');
        
        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        
        // Lowercase
        $text = strtolower($text);
        
        if (empty($text)) {
            return 'n-a';
        }
        
        return $text;
    }
    
    /**
     * Get the current URL
     * 
     * @param bool $withQueryString Whether to include query string
     * @return string Current URL
     */
    public static function getCurrentUrl($withQueryString = true) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        if (!$withQueryString) {
            $url = strtok($url, '?');
        }
        
        return $url;
    }
    
    /**
     * Get the base URL of the application
     * 
     * @return string Base URL
     */
    public static function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
    }
}
