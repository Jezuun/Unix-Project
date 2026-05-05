-- Initialize database for Unix Project
CREATE DATABASE IF NOT EXISTS unix_project;

USE unix_project;

-- Create a sample table for testing
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO users (username, email) VALUES 
    ('admin', 'admin@example.com'),
    ('user1', 'user1@example.com'),
    ('user2', 'user2@example.com');

-- Create a sample table for reservations (based on your existing code)
CREATE TABLE IF NOT EXISTS restaurant_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    party_size INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample reservation data
INSERT INTO restaurant_reservations (customer_name, customer_email, reservation_date, reservation_time, party_size) VALUES 
    ('John Doe', 'john@example.com', '2024-12-25', '19:00:00', 4),
    ('Jane Smith', 'jane@example.com', '2024-12-26', '20:00:00', 2);
