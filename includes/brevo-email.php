<?php


class BrevoEmailService {
    private $api_key;
    private $from_email;
    private $from_name;
    private $brevo_api = 'https://api.brevo.com/v3/smtp/email';
    
    public function __construct($api_key, $from_email, $from_name = 'Library Management System') {
        $this->api_key = $api_key;
        $this->from_email = $from_email;
        $this->from_name = $from_name;
    }
    
    
    public function send($to, $subject, $htmlBody, $textBody = null) {
        error_log("=== Brevo Email Send ===");
        error_log("To: " . $to);
        error_log("Subject: " . $subject);
        
        // Validate email
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("ERROR: Invalid recipient email: " . $to);
            return false;
        }
        
        // Prepare the email payload
        $payload = [
            'sender' => [
                'name' => $this->from_name,
                'email' => $this->from_email
            ],
            'to' => [
                [
                    'email' => $to
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlBody,
        ];
        
        if (!empty($textBody)) {
            $payload['textContent'] = $textBody;
        }
        
        // Send via Brevo API
        $response = $this->sendViaAPI($payload);
        
        if ($response === false) {
            error_log("ERROR: Brevo API failed, attempting fallback");
            // Fallback to PHP mail
            return $this->sendUsingPhpMail($to, $subject, $htmlBody);
        }
        
        return $response;
    }
    
    
    private function sendViaAPI($payload) {
        try {
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $this->brevo_api);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'api-key: ' . $this->api_key
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            
            curl_close($ch);
            
            if ($curl_error) {
                error_log("Brevo cURL error: " . $curl_error);
                return false;
            }
            
            error_log("Brevo HTTP Response Code: " . $http_code);
            
            $response_data = json_decode($response, true);
            
            if ($http_code == 201) {
                error_log("SUCCESS: Email sent via Brevo");
                error_log("Message ID: " . ($response_data['messageId'] ?? 'unknown'));
                error_log("Successfully sent to " . $payload['to'][0]['email']);
                return true;
            } else {
                error_log("Brevo error (" . $http_code . "): " . json_encode($response_data));
                
                if ($http_code == 401) {
                    error_log("ERROR: Invalid Brevo API Key");
                } elseif ($http_code == 400) {
                    error_log("ERROR: Bad request - check email format or Brevo config");
                }
                
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Brevo Exception: " . $e->getMessage());
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

