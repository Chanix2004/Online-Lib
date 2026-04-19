<?php

class Book {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addBook($isbn, $title, $author, $publisher, $publication_year, $category_id, $language, $pages, $edition, $total_copies, $purchase_price, $created_by, $description = '', $subcategory_id = null, $is_reference = false) {
        $sql = "INSERT INTO books (isbn, title, author, publisher, publication_year, category_id, subcategory_id, language, pages, edition, total_copies, available_copies, purchase_price, is_reference, description, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssissiiiiiiisi", $isbn, $title, $author, $publisher, $publication_year, $category_id, $subcategory_id, $language, $pages, $edition, $total_copies, $total_copies, $purchase_price, $is_reference, $description, $created_by);
        
        return $stmt->execute();
    }

    public function updateBook($book_id, $isbn, $title, $author, $publisher, $publication_year, $category_id, $language, $pages, $edition, $total_copies, $purchase_price, $description = '', $subcategory_id = null, $is_reference = false) {
        $sql = "UPDATE books SET isbn = ?, title = ?, author = ?, publisher = ?, publication_year = ?, category_id = ?, subcategory_id = ?, language = ?, pages = ?, edition = ?, total_copies = ?, purchase_price = ?, is_reference = ?, description = ?, updated_at = NOW()
                WHERE book_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssissiiiiiisi", $isbn, $title, $author, $publisher, $publication_year, $category_id, $subcategory_id, $language, $pages, $edition, $total_copies, $purchase_price, $is_reference, $description, $book_id);
        
        return $stmt->execute();
    }

    public function getAllBooks($limit = null, $offset = null) {
        $sql = "SELECT b.*, c.category_name, s.subcategory_name FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                LEFT JOIN subcategories s ON b.subcategory_id = s.subcategory_id";
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        return $this->conn->query($sql);
    }

    public function getBookById($book_id) {
        $sql = "SELECT b.*, c.category_name, s.subcategory_name FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                LEFT JOIN subcategories s ON b.subcategory_id = s.subcategory_id
                WHERE b.book_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    public function searchBooks($search_term) {
        $search = "%$search_term%";
        $sql = "SELECT b.*, c.category_name, s.subcategory_name FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                LEFT JOIN subcategories s ON b.subcategory_id = s.subcategory_id
                WHERE b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.description LIKE ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        
        return $stmt->get_result();
    }

    public function searchAndFilterBooks($search_term = '', $category_id = null, $limit = null, $offset = null) {
        $sql = "SELECT b.*, c.category_name, s.subcategory_name FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                LEFT JOIN subcategories s ON b.subcategory_id = s.subcategory_id
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        if (!empty($search_term)) {
            $search = "%$search_term%";
            $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.description LIKE ?)";
            $params = [$search, $search, $search, $search];
            $types = 'ssss';
        }
        
        if (!empty($category_id)) {
            $sql .= " AND b.category_id = ?";
            $params[] = $category_id;
            $types .= 'i';
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        return $stmt->get_result();
    }

    public function filterByCategory($category_id, $limit = null, $offset = null) {
        $sql = "SELECT b.*, c.category_name, s.subcategory_name FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                LEFT JOIN subcategories s ON b.subcategory_id = s.subcategory_id
                WHERE b.category_id = ?";
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        
        return $stmt->get_result();
    }

    public function getAvailableBooks() {
        $sql = "SELECT b.*, c.category_name FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                WHERE b.available_copies > 0";
        
        return $this->conn->query($sql);
    }

    public function uploadBookCover($book_id, $file) {

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024;
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['size'] > $max_size) {
            return false;
        }

        if (!in_array($file_ext, $allowed_types)) {
            return false;
        }

        $target_dir = __DIR__ . '/../' . BOOK_COVER_PATH;
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $filename = "book_" . $book_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {

            @chmod($target_file, 0644);  // Suppress warnings on shared hosting
            
            $sql = "UPDATE books SET book_cover = ? WHERE book_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $filename, $book_id);
            return $stmt->execute();
        }
        return false;
    }

    public function uploadBookPDF($book_id, $file) {

        $allowed_types = ['pdf'];
        $max_size = 50 * 1024 * 1024;
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['size'] > $max_size) {
            return false;
        }

        if (!in_array($file_ext, $allowed_types)) {
            return false;
        }

        $target_dir = __DIR__ . '/../' . PDF_PATH;
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $filename = "book_" . $book_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {

            @chmod($target_file, 0644);  // Suppress warnings on shared hosting
            
            $sql = "UPDATE books SET pdf_file = ? WHERE book_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $filename, $book_id);
            return $stmt->execute();
        }
        return false;
    }

    public function getBookCount() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM books");
        return $result->fetch_assoc()['count'];
    }

    public function deleteBook($book_id) {
        $sql = "DELETE FROM books WHERE book_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $book_id);
        return $stmt->execute();
    }
}

?>

