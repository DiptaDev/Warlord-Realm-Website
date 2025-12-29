CREATE DATABASE IF NOT EXISTS warlord_realm_gallery;
USE warlord_realm_gallery;

-- Tabel users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    minecraft_username VARCHAR(50),
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel images
CREATE TABLE images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    filename VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, password, email, is_admin) 
VALUES ('dipta14', '$2y$10$arA6qOHpGo.zhGawRy5kH.pbQksAySyvGlFUt.AKiHoaJF77o7dIa', 'sangsuratma@gmail.com', TRUE);

-- Insert sample user (password: user123)
INSERT INTO users (username, password, email, minecraft_username) 
VALUES ('user1', 'hash', 'player@example.com', 'MinecraftPlayer1');