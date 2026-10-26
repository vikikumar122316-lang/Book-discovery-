-- Book Discovery schema (structure only, no data)
CREATE DATABASE IF NOT EXISTS `book_discovery` DEFAULT CHARACTER SET utf8mb4;
USE `book_discovery`;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `authors`;
CREATE TABLE `authors` (
  `author_id` int NOT NULL AUTO_INCREMENT,
  `author_name` varchar(100) NOT NULL,
  `country` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `book_genres`;
CREATE TABLE `book_genres` (
  `book_id` int NOT NULL,
  `genre_id` int NOT NULL,
  PRIMARY KEY (`book_id`,`genre_id`),
  KEY `genre_id` (`genre_id`),
  CONSTRAINT `book_genres_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  CONSTRAINT `book_genres_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `book_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `author_id` int NOT NULL,
  `publication_year` int DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `pages` int DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`book_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `genres`;
CREATE TABLE `genres` (
  `genre_id` int NOT NULL AUTO_INCREMENT,
  `genre_name` varchar(50) NOT NULL,
  PRIMARY KEY (`genre_id`),
  UNIQUE KEY `genre_name` (`genre_name`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `preferences`;
CREATE TABLE `preferences` (
  `preference_id` int NOT NULL AUTO_INCREMENT,
  `reader_id` int NOT NULL,
  `genre_id` int DEFAULT NULL,
  `preferred_language` varchar(50) DEFAULT NULL,
  `minimum_rating` decimal(3,2) DEFAULT NULL,
  `preferred_length` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`preference_id`),
  KEY `reader_id` (`reader_id`),
  KEY `genre_id` (`genre_id`),
  CONSTRAINT `preferences_ibfk_1` FOREIGN KEY (`reader_id`) REFERENCES `readers` (`reader_id`),
  CONSTRAINT `preferences_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `readers`;
CREATE TABLE `readers` (
  `reader_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `language` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`reader_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `reader_id` int NOT NULL,
  `book_id` int NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `comment` text,
  `review_date` date DEFAULT NULL,
  PRIMARY KEY (`review_id`),
  KEY `reader_id` (`reader_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`reader_id`) REFERENCES `readers` (`reader_id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

SET FOREIGN_KEY_CHECKS=1;
