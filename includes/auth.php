<?php
session_start();

class Auth {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function register($full_name, $email, $phone, $password, $date_of_birth, $address, $city, $state, $postal_code, $country) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (full_name, email, phone, password_hash, date_of_birth, address, city, state, postal_code, country, role, membership_status, is_verified, verification_token, membership_number)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'member', 'active', FALSE, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $verification_token = bin2hex(random_bytes(16));
        $membership_number = 'MEM' . time();
        
        $stmt->bind_param("ssssssssssss", $full_name, $email, $phone, $password_hash, $date_of_birth, $address, $city, $state, $postal_code, $country, $verification_token, $membership_number);
        
        return $stmt->execute();
    }

    public function login($email, $password) {
        $sql = "SELECT user_id, full_name, email, password_hash, role, membership_status, profile_picture, is_verified FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                // Check if email is verified BEFORE setting session
                if (!$user['is_verified']) {
                    // Password correct but email not verified
                    return false;
                }
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_picture'] = $user['profile_picture'];
                $_SESSION['login_time'] = time();

                $update_sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                $update_stmt = $this->conn->prepare($update_sql);
                $update_stmt->bind_param("i", $user['user_id']);
                $update_stmt->execute();

                return $user; // Return full user object
            }
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION;
        }
        return null;
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function checkSessionTimeout() {
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
            $_SESSION['login_time'] = time();
            return true;
        }
        return false;
    }

    public function verifyEmail($token) {
        $sql = "UPDATE users SET is_verified = TRUE, verification_token = NULL WHERE verification_token = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        return $stmt->execute();
    }

    public function changePassword($user_id, $old_password, $new_password) {
        $sql = "SELECT password_hash FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($old_password, $user['password_hash'])) {
                $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $update_sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
                $update_stmt = $this->conn->prepare($update_sql);
                $update_stmt->bind_param("si", $new_password_hash, $user_id);
                return $update_stmt->execute();
            }
        }
        return false;
    }
}

?>

