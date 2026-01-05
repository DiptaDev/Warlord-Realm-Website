-- Database: warlord_realm_whitelist

-- Table: whitelist_applications
CREATE TABLE whitelist_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mc_username VARCHAR(50) NOT NULL,
    age INT NOT NULL,
    discord VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    play_duration VARCHAR(20) NOT NULL,
    play_time VARCHAR(20) NOT NULL,
    active_hours TEXT NOT NULL,
    consistency TEXT NOT NULL,
    server_experience TEXT NOT NULL,
    expertise TEXT,
    skill_level VARCHAR(30) NOT NULL,
    main_target TEXT NOT NULL,
    base_stolen TEXT NOT NULL,
    attitude_newbies TEXT NOT NULL,
    past_conflict TEXT NOT NULL,
    reaction_loss TEXT NOT NULL,
    fair_play TEXT NOT NULL,
    rule_violation TEXT NOT NULL,
    bug_response TEXT NOT NULL,
    admin_disagreement TEXT NOT NULL,
    important_rule TEXT NOT NULL,
    personality_type VARCHAR(30) NOT NULL,
    strength TEXT NOT NULL,
    weakness TEXT NOT NULL,
    contribution_willingness VARCHAR(50),
    contribution_type TEXT,
    agree_rules BOOLEAN DEFAULT 0,
    agree_sanctions BOOLEAN DEFAULT 0,
    agree_trial BOOLEAN DEFAULT 0,
    commitment_reason TEXT NOT NULL,
    why_accept TEXT NOT NULL,
    status ENUM('pending', 'interview', 'trial', 'approved', 'rejected') DEFAULT 'pending',
    application_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    INDEX idx_status (status),
    INDEX idx_mc_username (mc_username),
    INDEX idx_discord (discord),
    INDEX idx_email (email)
);

-- Table: admin_users
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admin_users (username, password_hash) VALUES 
('admin', '$2y$10$YourHashedPasswordHere');
-- Note: Generate password hash with password_hash('admin123', PASSWORD_DEFAULT)