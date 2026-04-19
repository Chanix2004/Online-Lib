<?php


class PostmarkEmailService {
    private $server_token;
    private $from_email;
    private $postmark_api = 'https://api.postmarkapp.com/email';
    
    public function __construct($server_token, $from_email) {
        $this->server_token = $server_token;
        $this->from_email = $from_email;
    }
    
    
    public function send($to, $subject, $htmlBody, $textBody = null) {
        error_log("=== Postmark Email Send ===");
        error_log("To: " . $to);
        error_log("Subject: " . $subject);
        
        // Validate email
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("ERROR: Invalid recipient email: " . $to);
            return false;
        }
        
        // Prepare the email payload
        $payload = [
            'From' => $this->from_email,
            'To' => $to,
            'Subject' => $subject,
            'HtmlBody' => $htmlBody,
        ];
        
        if (!empty($textBody)) {
            $payload['TextBody'] = $textBody;
        }
        
        // Send via Postmark API
        $response = $this->sendViaAPI($payload);
        
        if ($response === false) {
            error_log("ERROR: Postmark API failed, attempting fallback");
            // Fallback to PHP mail
            return $this->sendUsingPhpMail($to, $subject, $htmlBody);
        }
        
        return $response;
    }
    
    
    private function sendViaAPI($payload) {
        try {
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $this->postmark_api);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'X-Postmark-Server-Token: ' . $this->server_token
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            
            curl_close($ch);
            
            if ($curl_error) {
                error_log("Postmark cURL error: " . $curl_error);
                return false;
            }
            
            error_log("Postmark HTTP Response Code: " . $http_code);
            
            $response_data = json_decode($response, true);
            
            if ($http_code == 200) {
                error_log("SUCCESS: Email sent via Postmark");
                error_log("Message ID: " . ($response_data['MessageID'] ?? 'unknown'));
                error_log("Successfully sent to " . $payload['To']);
                return true;
            } else {
                error_log("Postmark error (" . $http_code . "): " . json_encode($response_data));
                
                if ($http_code == 401) {
                    error_log("ERROR: Invalid Postmark Server Token");
                } elseif ($http_code == 422) {
                    error_log("ERROR: Invalid email address or Postmark config");
                }
                
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Postmark Exception: " . $e->getMessage());
            return false;
        }
    }
    
    
    private function sendUsingPhpMail($to, $subject, $htmlBody) {
        error_log("Falling back to PHP mail()");
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $this->from_email . "\r\n";
        $headers .= "Reply-To: " . $this->from_email . "\r\n";
        $headers .= "X-Mailer: LibraryManagementSystem\r\n";
        
        $result = mail($to, $subject, $htmlBody, $headers);
        
        if ($result) {
            error_log("PHP mail() successful for " . $to);
        } else {
            error_log("PHP mail() failed for " . $to);
        }
        
        return $result;
    }
}
?>

