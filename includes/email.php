<?php

class EmailService {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_from;
    private $smtp_encryption;
    
    public function __construct($host, $port, $username, $password, $from, $encryption = 'tls') {
        $this->smtp_host = $host;
        $this->smtp_port = $port;
        $this->smtp_username = $username;
        $this->smtp_password = $password;
        $this->smtp_from = $from;
        $this->smtp_encryption = $encryption;
    }
    
    
    public function send($to, $subject, $htmlBody, $textBody = null) {
        error_log("=== EmailService::send() CALLED ===");
        error_log("To: " . $to);
        error_log("Subject: " . $subject);
        
        // If SMTP is not configured, use PHP mail()
        if (empty($this->smtp_username) || empty($this->smtp_password)) {
            error_log("Using PHP mail() - SMTP not configured");
            return $this->sendUsingPhpMail($to, $subject, $htmlBody);
        }
        
        // Try SMTP connection
        error_log("Attempting SMTP send...");
        $result = $this->sendUsingSMTP($to, $subject, $htmlBody);
        
        // If SMTP fails, fallback to PHP mail()
        if (!$result) {
            error_log("Email: SMTP failed, falling back to PHP mail()");
            return $this->sendUsingPhpMail($to, $subject, $htmlBody);
        }
        
        return $result;
    }
    
    
    private function sendUsingPhpMail($to, $subject, $htmlBody) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $this->smtp_from . "\r\n";
        $headers .= "Reply-To: " . $this->smtp_from . "\r\n";
        $headers .= "X-Mailer: LibraryManagementSystem\r\n";
        
        return mail($to, $subject, $htmlBody, $headers);
    }
    
    
    private function sendUsingSMTP($to, $subject, $htmlBody) {
        try {
            // Connect to SMTP server
            $socket = @fsockopen(
                $this->smtp_host,
                $this->smtp_port,
                $errno,
                $errstr,
                30
            );
            
            if (!$socket) {
                error_log("Email: Failed to connect to " . $this->smtp_host . ":" . $this->smtp_port . " - $errstr ($errno)");
                return false;
            }
            
            // Get server response
            $response = fgets($socket, 512);
            if (strpos($response, '220') === false) {
                error_log("Email: SMTP server greeting error: " . trim($response));
                fclose($socket);
                return false;
            }
            
            // Send EHLO
            fputs($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 512);
            
            // Read all EHLO responses
            while (substr($response, 3, 1) === '-') {
                $response = fgets($socket, 512);
            }
            
            // Authenticate
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 512);
            
            fputs($socket, base64_encode($this->smtp_username) . "\r\n");
            $response = fgets($socket, 512);
            
            fputs($socket, base64_encode($this->smtp_password) . "\r\n");
            $response = fgets($socket, 512);
            
            if (strpos($response, '235') === false && strpos($response, '334') === false) {
                error_log("Email: AUTH failed: " . trim($response));
                fclose($socket);
                return false;
            }
            
            // Send MAIL FROM
            fputs($socket, "MAIL FROM:<" . $this->smtp_from . ">\r\n");
            $response = fgets($socket, 512);
            if (strpos($response, '250') === false) {
                error_log("Email: MAIL FROM error: " . trim($response));
                fclose($socket);
                return false;
            }
            
            // Send RCPT TO
            fputs($socket, "RCPT TO:<" . $to . ">\r\n");
            $response = fgets($socket, 512);
            if (strpos($response, '250') === false) {
                error_log("Email: RCPT TO error: " . trim($response));
                fclose($socket);
                return false;
            }
            
            // Send DATA
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 512);
            if (strpos($response, '354') === false) {
                error_log("Email: DATA error: " . trim($response));
                fclose($socket);
                return false;
            }
            
            // Build email headers
            $headers = "From: " . $this->smtp_from . "\r\n";
            $headers .= "To: " . $to . "\r\n";
            $headers .= "Subject: " . $subject . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "X-Mailer: LibraryManagementSystem\r\n";
            
            // Send message
            fputs($socket, $headers . "\r\n" . $htmlBody . "\r\n.\r\n");
            $response = fgets($socket, 512);
            
            if (strpos($response, '250') === false) {
                error_log("Email: Message send error: " . trim($response));
                fclose($socket);
                return false;
            }
            
            // QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            error_log("Email: Successfully sent to $to");
            return true;
            
        } catch (Exception $e) {
            error_log("Email Exception: " . $e->getMessage());
            return false;
        }
    }
}
?>

