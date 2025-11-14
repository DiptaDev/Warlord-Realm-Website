-- database_setup.sql
CREATE DATABASE IF NOT EXISTS warlord_realm;
USE warlord_realm;

CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL,
    minecraft_type ENUM('original', 'cracked') NOT NULL,
    discord_username VARCHAR(100) NOT NULL,
    social_media VARCHAR(255),
    skills TEXT,
    experience TEXT,
    reason TEXT,
    diamond_preference VARCHAR(50),
    admin_message TEXT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT
);

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin dengan password yang di-hash
-- Password: warlord123
INSERT INTO admin_users (username, password_hash) 
VALUES 
('admin', '$2y$10$asRqMJ2yiQO1kNWlMby35.Wrx2P/HtpfY7Hg.2ohc7TJmQXplJ8d.'); -- may need some fixes when deployed