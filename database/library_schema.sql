-- MySQL dump for library management system
-- Database: library_schema

CREATE DATABASE IF NOT EXISTS library_schema;
USE library_schema;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `borrowings`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `genres`;
DROP TABLE IF EXISTS `authors`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `authors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `genres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL UNIQUE,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `books` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `author_id` int NOT NULL,
  `genre_id` int NOT NULL,
  `year` int NOT NULL,
  `isbn` varchar(13) NOT NULL UNIQUE,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `books_authors_FK` (`author_id`),
  KEY `books_genres_FK` (`genre_id`),
  CONSTRAINT `books_authors_FK` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `books_genres_FK` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `borrowings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `borrow_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `borrowings_users_FK` (`user_id`),
  KEY `borrowings_books_FK` (`book_id`),
  CONSTRAINT `borrowings_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `borrowings_books_FK` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES
(1, 'Admin User', 'admin@test.com', '1234567890', '$2y$10$vYW1XZTzf8qPj2YGzG5ce.V7FGmEYB6EGLfXXY9YcYjEHvJ2j4Pze', 1, NOW(), NOW()),
(2, 'Regular User', 'user@test.com', '0987654321', '$2y$10$SlXb8b1c6DpuqEmhqSpM9.VQ5xvJ7j4vfEIhcsVEEBqS1S7z4y8O2', 0, NOW(), NOW());

INSERT INTO `authors` VALUES
(1, 'J.K. Rowling', 'United Kingdom', NOW(), NOW()),
(2, 'George R.R. Martin', 'United States', NOW(), NOW()),
(3, 'J.R.R. Tolkien', 'United Kingdom', NOW(), NOW()),
(4, 'Stephen King', 'United States', NOW(), NOW()),
(5, 'Haruki Murakami', 'Japan', NOW(), NOW());

INSERT INTO `genres` VALUES
(1, 'Fantasy', NOW(), NOW()),
(2, 'Science Fiction', NOW(), NOW()),
(3, 'Mystery', NOW(), NOW()),
(4, 'Romance', NOW(), NOW()),
(5, 'Horror', NOW(), NOW()),
(6, 'Adventure', NOW(), NOW());

INSERT INTO `books` VALUES
(1, 'Harry Potter and the Philosophers Stone', 1, 1, 1997, '9780747532699', NOW(), NOW()),
(2, 'A Game of Thrones', 2, 1, 1996, '9780553103540', NOW(), NOW()),
(3, 'The Lord of the Rings', 3, 1, 1954, '9780544003415', NOW(), NOW()),
(4, 'The Shining', 4, 5, 1977, '9780385333312', NOW(), NOW()),
(5, 'Norwegian Wood', 5, 4, 1987, '9780375704024', NOW(), NOW()),
(6, 'The Hobbit', 3, 1, 1937, '9780547928227', NOW(), NOW()),
(7, 'It', 4, 5, 1986, '9780451191144', NOW(), NOW()),
(8, '1Q84', 5, 2, 2009, '9780307476463', NOW(), NOW());

INSERT INTO `borrowings` VALUES
(1, 2, 1, '2025-12-01', NULL, NOW(), NOW()),
(2, 2, 3, '2025-12-05', '2025-12-20', NOW(), NOW()),
(3, 2, 5, '2025-12-10', NULL, NOW(), NOW());
