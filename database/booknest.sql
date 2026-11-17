-- BookNest database
CREATE DATABASE IF NOT EXISTS booknest;
USE booknest;

DROP TABLE IF EXISTS fines;
DROP TABLE IF EXISTS issued_books;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

-- users table (admin and students)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','student') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- books table
CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  author VARCHAR(100) NOT NULL,
  isbn VARCHAR(20),
  category VARCHAR(50) NOT NULL,
  total_copies INT NOT NULL DEFAULT 1,
  available_copies INT NOT NULL DEFAULT 1,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- one row for every time a book is issued to a user
CREATE TABLE issued_books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  user_id INT NOT NULL,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  return_date DATE NULL,
  status ENUM('issued','returned') NOT NULL DEFAULT 'issued',
  FOREIGN KEY (book_id) REFERENCES books(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- fine is made when a book is returned late
CREATE TABLE fines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  issue_id INT NOT NULL,
  amount DECIMAL(8,2) NOT NULL,
  status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  FOREIGN KEY (issue_id) REFERENCES issued_books(id)
);

-- sample data
-- admin password: admin123 , student password: student123
INSERT INTO users (name, email, password, role) VALUES
('Admin User',  'admin@booknest.com', '$2y$10$EM5V3Hl9qHpp7VVOF6ToBurvf6uchxrn4s/kViLlqhkyVHvPfyveK', 'admin'),
('Ali Khan',    'ali@example.com',    '$2y$10$9FQ8PxWdkB9eDEUMOd9hlO2jit5uaYp3mAWC45ZrOLyswIphBGhhu', 'student'),
('Sara Ahmed',  'sara@example.com',   '$2y$10$9FQ8PxWdkB9eDEUMOd9hlO2jit5uaYp3mAWC45ZrOLyswIphBGhhu', 'student'),
('Hamza Raza',  'hamza@example.com',  '$2y$10$9FQ8PxWdkB9eDEUMOd9hlO2jit5uaYp3mAWC45ZrOLyswIphBGhhu', 'student');

INSERT INTO books (title, author, isbn, category, total_copies, available_copies, description) VALUES
('Database Systems',      'Elmasri',           '9780133970777', 'Computer Science', 3, 2, 'Basics of databases and SQL.'),
('Data Structures',       'Seymour Lipschutz', '9780070991309', 'Computer Science', 2, 1, 'Arrays, linked lists, trees and graphs.'),
('Operating Systems',     'Galvin',            '9781118063330', 'Computer Science', 2, 2, 'Processes, memory and file systems.'),
('Web Development Basics','Jon Duckett',       '9781118008188', 'Web',              4, 4, 'HTML and CSS for beginners.'),
('Calculus',              'Thomas',            '9780321587992', 'Mathematics',      2, 2, 'Limits, derivatives and integrals.'),
('Discrete Mathematics',  'Rosen',             '9780073383095', 'Mathematics',      3, 3, 'Logic, sets and graphs.');

INSERT INTO issued_books (book_id, user_id, issue_date, due_date, return_date, status) VALUES
(2, 3, CURDATE() - INTERVAL 25 DAY, CURDATE() - INTERVAL 10 DAY, NULL, 'issued'),
(1, 2, CURDATE() - INTERVAL 5 DAY,  CURDATE() + INTERVAL 9 DAY,  NULL, 'issued'),
(5, 2, CURDATE() - INTERVAL 40 DAY, CURDATE() - INTERVAL 26 DAY, CURDATE() - INTERVAL 21 DAY, 'returned'),
(3, 4, CURDATE() - INTERVAL 30 DAY, CURDATE() - INTERVAL 16 DAY, CURDATE() - INTERVAL 11 DAY, 'returned');

INSERT INTO fines (issue_id, amount, status) VALUES
(3, 50.00, 'paid'),
(4, 50.00, 'unpaid');
