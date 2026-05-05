-- Initialize database for Unix Project
CREATE DATABASE IF NOT EXISTS unix_project;
USE unix_project;

-- Users table
CREATE TABLE IF NOT EXISTS users (
                                   id INT AUTO_INCREMENT PRIMARY KEY,
                                   username VARCHAR(50) NOT NULL UNIQUE,
                                   email VARCHAR(100) NOT NULL UNIQUE,
                                   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, email) VALUES
                                      ('admin', 'admin@example.com'),
                                      ('user1', 'user1@example.com'),
                                      ('user2', 'user2@example.com');

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
                                          id INT AUTO_INCREMENT PRIMARY KEY,
                                          name VARCHAR(100) NOT NULL,
                                          email VARCHAR(100) NOT NULL,
                                          phone VARCHAR(20) NOT NULL,
                                          guests INT NOT NULL,
                                          reservation_date DATE NOT NULL,
                                          reservation_time TIME NOT NULL,
                                          notes TEXT,
                                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
