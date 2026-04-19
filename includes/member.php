<?php

class Member {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllMembers($limit = null, $offset = null) {
        $sql = "SELECT * FROM users WHERE role = 'member'";
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        return $this->conn->query($sql);
    }

    public function getMemberById($user_id) {
        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateMemberProfile($user_id, $full_name, $phone, $address, $city, $state, $postal_code, $country) {
        $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, city = ?, state = ?, postal_code = ?, country = ?, updated_at = NOW()
                WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssi", $full_name, $phone, $address, $city, $state, $postal_code, $country, $user_id);
        
        return $stmt->execute();
    }

    public function uploadProfilePicture($user_id, $file) {

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024;
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['size'] > $max_size) {
            return false;
        }

        if (!in_array($file_ext, $allowed_types)) {
            return false;
        }

        $target_dir = __DIR__ . '/../' . PROFILE_PIC_PATH;
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $filename = "profile_" . $user_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {

            @chmod($target_file, 0644);  // Suppress warnings on shared hosting
            
            $sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $filename, $user_id);
            return $stmt->execute();
        }
        return false;
    }

    public function suspendMember($user_id, $reason = '') {
        $sql = "UPDATE users SET membership_status = 'suspended', updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        return $stmt->execute();
    }

    public function activateMember($user_id) {
        $sql = "UPDATE users SET membership_status = 'active', updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        return $stmt->execute();
    }

    public function getMemberCount() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'member'");
        return $result->fetch_assoc()['count'];
    }

}

?>

