-- Create Database
CREATE DATABASE IF NOT EXISTS library_management;
USE library_management;

-- Users Table (Members/Admins)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(10),
    country VARCHAR(100),
    profile_picture VARCHAR(255),
    role ENUM('admin', 'librarian', 'member') DEFAULT 'member',
    membership_status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    membership_number VARCHAR(50) UNIQUE,
    membership_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE,
    outstanding_fine DECIMAL(10, 2) DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Books Table
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    publisher VARCHAR(255),
    publication_year INT,
    book_cover VARCHAR(255),
    description LONGTEXT,
    category_id INT,
    subcategory_id INT,
    language VARCHAR(50) DEFAULT 'English',
    pages INT,
    edition VARCHAR(50),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    pdf_file VARCHAR(255),
    rack_number VARCHAR(50),
    shelf_number VARCHAR(50),
    purchase_price DECIMAL(10, 2),
    is_reference BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_title (title),
    INDEX idx_author (author),
    INDEX idx_isbn (isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sub Categories Table
CREATE TABLE subcategories (
    subcategory_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    subcategory_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    UNIQUE KEY unique_subcat (category_id, subcategory_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Book Borrowing Table
CREATE TABLE borrowings (
    borrowing_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE NOT NULL,
    return_date DATE,
    fine_amount DECIMAL(10, 2) DEFAULT 0,
    fine_paid BOOLEAN DEFAULT FALSE,
    status ENUM('borrowed', 'returned', 'overdue', 'lost') DEFAULT 'borrowed',
    notes TEXT,
    created_by INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Book Reservations Table
CREATE TABLE reservations (
    reservation_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'ready', 'cancelled', 'expired') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_reservation (user_id, book_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fine Records Table
CREATE TABLE fines (
    fine_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    borrowing_id INT,
    fine_amount DECIMAL(10, 2) NOT NULL,
    fine_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE,
    paid_date DATE,
    payment_method ENUM('cash', 'card', 'check', 'online') DEFAULT 'cash',
    reference_number VARCHAR(100),
    notes TEXT,
    status ENUM('pending', 'paid', 'waived', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (borrowing_id) REFERENCES borrowings(borrowing_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message LONGTEXT NOT NULL,
    type ENUM('reminder', 'alert', 'info', 'success') DEFAULT 'info',
    related_entity_type VARCHAR(50),
    related_entity_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_read (is_read),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Log Table
CREATE TABLE activity_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details LONGTEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_entity (entity_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports Table
CREATE TABLE reports (
    report_id INT PRIMARY KEY AUTO_INCREMENT,
    report_type VARCHAR(100) NOT NULL,
    report_name VARCHAR(255) NOT NULL,
    filters LONGTEXT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    generated_by INT NOT NULL,
    file_path VARCHAR(255),
    FOREIGN KEY (generated_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type VARCHAR(50),
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Library Management System Database
-- Clean Updated Version (Only Used Tables)
-- ========================================

-- Create Database
CREATE DATABASE IF NOT EXISTS library_management;
USE library_management;

-- ========================================
-- TABLES DEFINITION (CLEAN)
-- ========================================

-- Users Table (Members/Admins/Librarians)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(10),
    country VARCHAR(100),
    profile_picture VARCHAR(255),
    role ENUM('admin', 'librarian', 'member') DEFAULT 'member',
    membership_status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    membership_number VARCHAR(50) UNIQUE,
    membership_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE,
    outstanding_fine DECIMAL(10, 2) DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_role (role),
    INDEX idx_email (email),
    INDEX idx_membership_number (membership_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sub Categories Table
CREATE TABLE subcategories (
    subcategory_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    subcategory_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    UNIQUE KEY unique_subcat (category_id, subcategory_name),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Books Table
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    publisher VARCHAR(255),
    publication_year INT,
    book_cover VARCHAR(255),
    description LONGTEXT,
    category_id INT,
    subcategory_id INT,
    language VARCHAR(50) DEFAULT 'English',
    pages INT,
    edition VARCHAR(50),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    pdf_file VARCHAR(255),
    rack_number VARCHAR(50),
    shelf_number VARCHAR(50),
    purchase_price DECIMAL(10, 2),
    is_reference BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (subcategory_id) REFERENCES subcategories(subcategory_id),
    INDEX idx_title (title),
    INDEX idx_author (author),
    INDEX idx_isbn (isbn),
    INDEX idx_available_copies (available_copies)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Book Borrowing Table
CREATE TABLE borrowings (
    borrowing_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE NOT NULL,
    return_date DATE,
    fine_amount DECIMAL(10, 2) DEFAULT 0,
    fine_paid BOOLEAN DEFAULT FALSE,
    status ENUM('borrowed', 'returned', 'overdue', 'lost') DEFAULT 'borrowed',
    notes TEXT,
    created_by INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Book Reservations Table
CREATE TABLE reservations (
    reservation_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'ready', 'cancelled', 'expired') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_reservation (user_id, book_id),
    INDEX idx_status (status),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SAMPLE DATA
-- ========================================

-- Admin User (System Administrator)
-- Password: admin (hashed with bcrypt)
INSERT IGNORE INTO users (user_id, full_name, email, phone, password_hash, role, membership_status, membership_number, membership_date, created_at, updated_at) 
VALUES (1, 'System Administrator', 'admin@librarysystem.com', NULL, '$2y$10$R6VGb.OQlOn8OmIOMO.JFOnZ0nBEZnEdqdIbQtYPixmA.7IcHqH2y', 'admin', 'active', 'ADM-001', NOW(), NOW(), NOW());

-- Member User (Rod Christian Mojado)
INSERT IGNORE INTO users (user_id, full_name, email, phone, password_hash, role, membership_status, membership_number, membership_date, is_verified, created_at, updated_at) 
VALUES (2, 'Rod Christian Mojado', 'rohan.14yahoo@gmail.com', NULL, '$2y$10$R6VGb.OQlOn8OmIOMO.JFOnZ0nBEZnEdqdIbQtYPixmA.7IcHqH2y', 'member', 'active', 'MEM1773923430', '2026-03-19', TRUE, '2026-03-19 20:30:30', NOW());

-- Categories
INSERT IGNORE INTO categories (category_id, category_name, description) VALUES
(1, 'Fiction', 'Fictional novels and stories'),
(2, 'Non-Fiction', 'Non-fictional books and references'),
(3, 'Science', 'Science and technology books'),
(4, 'History', 'Historical books and references'),
(5, 'Self-Help', 'Self-help and personal development');

-- SubCategories
INSERT IGNORE INTO subcategories (subcategory_id, category_id, subcategory_name, description) VALUES
(1, 1, 'Romance', 'Romantic novels and love stories'),
(2, 1, 'Mystery', 'Mystery and detective novels'),
(3, 1, 'Fantasy', 'Fantasy and magical stories'),
(4, 2, 'Biography', 'Biographical works and memoirs'),
(5, 2, 'Self-Help', 'Practical self-improvement guides'),
(6, 3, 'Physics', 'Physics and astronomy books'),
(7, 3, 'Biology', 'Biology and natural sciences'),
(8, 4, 'Ancient History', 'Ancient civilizations and history'),
(9, 4, 'Modern History', 'Modern era and contemporary history'),
(10, 5, 'Motivation', 'Motivational and inspirational books');

-- Sample Books
INSERT IGNORE INTO books (isbn, title, author, publisher, publication_year, language, pages, edition, total_copies, available_copies, purchase_price, category_id, subcategory_id, description, pdf_file, created_by) 
VALUES
('978-0-545-01022-1', 'The Great Gatsby', 'F. Scott Fitzgerald', 'Scribner', 1925, 'English', 180, '1st', 5, 5, 15.99, 1, 1, 'A classic American novel set during the Jazz Age in New York. The story follows Jay Gatsby and his obsessive love for Daisy Buchanan.', 'the-great-gatsby.pdf', 1),
('978-0-14-118277-0', 'Pride and Prejudice', 'Jane Austen', 'Penguin Classics', 1813, 'English', 432, '1st', 4, 4, 12.99, 1, 1, 'A romantic novel of manners and marriage set in Georgian England, exploring themes of love, friendship, and social class.', 'pride-and-prejudice.pdf', 1),
('978-0-7432-7356-5', '1984', 'George Orwell', 'Penguin Books', 1949, 'English', 328, '1st', 6, 6, 14.99, 1, 3, 'A dystopian novel set in a totalitarian superstate. The story follows Winston Smith as he attempts to rebel against the oppressive regime.', '1984.pdf', 1),
('978-0-06-112008-4', 'To Kill a Mockingbird', 'Harper Lee', 'HarperCollins', 1960, 'English', 324, '1st', 5, 5, 16.99, 1, 2, 'A gripping tale of racial injustice and childhood innocence in the Deep South. Narrated by Scout Finch as she learns about morality and prejudice.', 'to-kill-a-mockingbird.pdf', 1),
('978-0-14-118706-5', 'Moby Dick', 'Herman Melville', 'Penguin Classics', 1851, 'English', 585, '1st', 3, 3, 18.99, 1, 3, 'An epic novel about Captain Ahab obsessive quest to hunt down the white whale Moby Dick, exploring themes of obsession and fate.', 'moby-dick.pdf', 1),
('978-0-7434-2817-1', 'The Catcher in the Rye', 'J.D. Salinger', 'Little, Brown', 1951, 'English', 277, '1st', 4, 4, 15.99, 1, 2, 'A coming-of-age novel following Holden Caulfield through his emotional and stressful journey after being expelled from school.', 'the-catcher-in-the-rye.pdf', 1),
('978-0-06-093546-7', 'A Brief History of Time', 'Stephen Hawking', 'Bantam Books', 1988, 'English', 237, '1st', 3, 3, 18.99, 3, 6, 'A landmark volume in science writing explaining complex cosmological concepts in accessible language.', 'brief-history-of-time.pdf', 1),
('978-0-14-028329-7', 'The Odyssey', 'Homer', 'Penguin Classics', 800, 'English', 375, '1st', 3, 3, 14.99, 4, 8, 'An epic Greek poem following Odysseus on his journey home after the Trojan War, filled with adventure and mythological encounters.', 'the-odyssey.pdf', 1),
('978-0-345-33312-0', 'Dune', 'Frank Herbert', 'ACE', 1965, 'English', 682, '1st', 4, 4, 17.99, 1, 3, 'A science fiction masterpiece set on the desert planet Arrakis, exploring themes of politics, religion, and ecology.', 'dune.pdf', 1),
('978-0-7432-7357-2', 'Wuthering Heights', 'Emily Brontë', 'Penguin Classics', 1847, 'English', 352, '1st', 3, 3, 13.99, 1, 1, 'A dark gothic novel set in Yorkshire, exploring themes of passion, revenge, and social class through the turbulent relationship of Heathcliff and Catherine.', 'wuthering-heights.pdf', 1);

-- Insert Default Admin
INSERT INTO users (full_name, email, password_hash, role, membership_status, is_verified)
VALUES ('Admin User', 'admin@librarysystem.com', '$2y$10$JyQk6jKHjrVvqhL8vG.kYubCcPvH0WnFqj3u0rHXoWL2p8Z5X2iJS', 'admin', 'active', TRUE);

-- Insert Sample Categories
INSERT INTO categories (category_name, description) VALUES
('Fiction', 'Fictional novels and stories'),
('Non-Fiction', 'Educational and informational books'),
('Science', 'Science and technology books'),
('History', 'Historical books and references'),
('Biography', 'Life stories and autobiographies'),
('Reference', 'Reference materials and guides'),
('Children', 'Books for children'),
('Academic', 'Academic and educational materials');

-- Create Views for Reports
CREATE VIEW overdue_books AS
SELECT 
    b.borrowing_id,
    u.user_id,
    u.full_name,
    u.email,
    bk.title,
    bk.book_id,
    b.due_date,
    DATEDIFF(CURDATE(), b.due_date) as days_overdue
FROM borrowings b
JOIN users u ON b.user_id = u.user_id
JOIN books bk ON b.book_id = bk.book_id
WHERE b.status IN ('borrowed', 'overdue') AND b.due_date < CURDATE();

CREATE VIEW active_borrowings AS
SELECT 
    b.borrowing_id,
    u.full_name,
    bk.title,
    b.borrow_date,
    b.due_date,
    b.status
FROM borrowings b
JOIN users u ON b.user_id = u.user_id
JOIN books bk ON b.book_id = bk.book_id
WHERE b.status = 'borrowed';

CREATE VIEW book_availability AS
SELECT 
    book_id,
    title,
    total_copies,
    available_copies,
    (total_copies - available_copies) as copies_borrowed,
    ROUND((available_copies / total_copies * 100), 2) as availability_percentage
FROM books;
