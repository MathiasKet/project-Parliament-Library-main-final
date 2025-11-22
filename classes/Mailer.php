<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
    private $settings;
    private $errors = [];
    private $debug = false;
    private static $instance = null;

    private function __construct() {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $this->mailer = new PHPMailer(true);
        $this->settings = Settings::getInstance();
        
        $this->configure();
    }

    // Get singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Configure mailer with settings
    private function configure() {
        try {
            // Server settings
            $this->mailer->SMTPDebug = $this->debug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
            $this->mailer->isSMTP();
            
            // SMTP settings from config
            $this->mailer->Host = $this->settings->smtp_host;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->settings->smtp_username;
            $this->mailer->Password = $this->settings->smtp_password;
            $this->mailer->SMTPSecure = $this->settings->smtp_encryption;
            $this->mailer->Port = $this->settings->smtp_port;
            
            // Default from address
            $this->mailer->setFrom(
                $this->settings->from_email, 
                $this->settings->from_name
            );
            
            // Set default reply-to
            $this->mailer->addReplyTo(
                $this->settings->from_email, 
                $this->settings->from_name
            );
            
            // Set content type to HTML
            $this->mailer->isHTML(true);
            
        } catch (Exception $e) {
            $this->errors[] = "Mailer configuration error: " . $e->getMessage();
            error_log("Mailer Error: " . $e->getMessage());
        }
    }

    // Set debug mode
    public function setDebug($debug) {
        $this->debug = (bool)$debug;
        $this->mailer->SMTPDebug = $this->debug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
        return $this;
    }

    // Send an email
    public function send($to, $subject, $body, $altBody = null, $attachments = []) {
        try {
            // Reset all addresses and attachments
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->errors = [];
            
            // Set recipients
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }
            
            // Set subject and body
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);
            
            // Add attachments if any
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        $this->mailer->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? '',
                            $attachment['encoding'] ?? 'base64',
                            $attachment['type'] ?? '',
                            $attachment['disposition'] ?? 'attachment'
                        );
                    } else {
                        $this->mailer->addAttachment($attachment);
                    }
                }
            }
            
            // Send the email
            $result = $this->mailer->send();
            
            if (!$result) {
                $this->errors[] = 'Failed to send email';
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }

    // Send a templated email
    public function sendTemplate($to, $template, $data = [], $attachments = []) {
        $templatePath = __DIR__ . "/../templates/emails/$template.php";
        
        if (!file_exists($templatePath)) {
            $this->errors[] = "Email template not found: $template";
            return false;
        }
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the template file
        include $templatePath;
        
        // Get the contents of the buffer
        $body = ob_get_clean();
        
        // Get subject from template if not provided
        if (empty($data['subject'])) {
            $subject = $this->settings->site_name;
            
            // Try to extract subject from template
            if (preg_match('/<title>(.*?)<\/title>/is', $body, $matches)) {
                $subject = trim($matches[1]);
                // Remove title from body
                $body = preg_replace('/<title>.*?<\/title>/is', '', $body, 1);
            }
        } else {
            $subject = $data['subject'];
        }
        
        // Send the email
        return $this->send($to, $subject, $body, null, $attachments);
    }

    // Get the last error message
    public function getLastError() {
        return end($this->errors) ?: '';
    }

    // Get all error messages
    public function getErrors() {
        return $this->errors;
    }

    // Check if there are any errors
    public function hasErrors() {
        return !empty($this->errors);
    }

    // Send a test email
    public function sendTestEmail($to) {
        $subject = 'Test Email from ' . $this->settings->site_name;
        $body = '<h1>Test Email</h1>';
        $body .= '<p>This is a test email sent from ' . $this->settings->site_name . '.</p>';
        $body .= '<p>If you received this email, your SMTP settings are configured correctly.</p>';
        $body .= '<p>Time sent: ' . date('Y-m-d H:i:s') . '</p>';
        
        return $this->send($to, $subject, $body);
    }

    // Send password reset email
    public function sendPasswordResetEmail($userEmail, $resetToken) {
        $resetLink = SITE_URL . '/reset-password.php?token=' . urlencode($resetToken);
        
        $data = [
            'subject' => 'Password Reset Request',
            'user' => ['email' => $userEmail],
            'reset_link' => $resetLink,
            'expiry_hours' => 24 // Default expiry time
        ];
        
        return $this->sendTemplate($userEmail, 'password-reset', $data);
    }

    // Send welcome email to new users
    public function sendWelcomeEmail($userEmail, $userName) {
        $data = [
            'subject' => 'Welcome to ' . $this->settings->site_name,
            'user' => [
                'name' => $userName,
                'email' => $userEmail
            ],
            'login_url' => SITE_URL . '/login.php'
        ];
        
        return $this->sendTemplate($userEmail, 'welcome', $data);
    }

    // Send book due reminder
    public function sendBookDueReminder($userEmail, $userName, $bookTitle, $dueDate) {
        $data = [
            'subject' => 'Book Due Reminder: ' . $bookTitle,
            'user' => ['name' => $userName],
            'book_title' => $bookTitle,
            'due_date' => date('F j, Y', strtotime($dueDate)),
            'return_url' => SITE_URL . '/my-account.php'
        ];
        
        return $this->sendTemplate($userEmail, 'book-due-reminder', $data);
    }
}
