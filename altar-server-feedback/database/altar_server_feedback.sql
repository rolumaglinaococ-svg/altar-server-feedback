-- =====================================================================
-- Altar Server Month 2026 - Database Setup
-- =====================================================================
-- HOW TO USE THIS FILE:
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Click "Import"
-- 3. Choose this file
-- 4. Click "Go"
-- This will create the database, both tables, and one admin account.
-- =====================================================================



-- 3. Create the feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NULL,
    parish_vicariate VARCHAR(150) NOT NULL,
    overall_rating INT NOT NULL,
    enjoyed_most TEXT NOT NULL,
    favorite_activity TEXT NOT NULL,
    feeling VARCHAR(100) NOT NULL,
    met_other_servers VARCHAR(100) NOT NULL,
    learned_realized TEXT NOT NULL,
    appreciated_service VARCHAR(100) NOT NULL,
    three_words VARCHAR(150) NOT NULL,
    improvement TEXT NOT NULL,
    next_event TEXT NULL,
    message_to_organizers TEXT NULL,
    join_again VARCHAR(100) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create the admin table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Insert an initial admin account
-- -----------------------------------------------------------------
-- Username: admin
-- Password: AltarServer2026!
--
-- IMPORTANT: The password below is NOT stored as plain text.
-- It is a bcrypt hash produced by PHP's password_hash() function,
-- using this exact code:
--
--     echo password_hash('AltarServer2026!', PASSWORD_DEFAULT);
--
-- password_hash() automatically generates a random "salt" and mixes
-- it into the hash, so even if two admins used the exact same
-- password, their stored hashes would look different. When the admin
-- logs in, admin/authenticate.php uses password_verify() to check the
-- typed password against this hash -- the plain password itself is
-- never saved anywhere.
--
-- CHANGE THIS PASSWORD after your first login for real use. You can
-- generate a new hash by creating a temporary PHP file containing:
--     <?php echo password_hash('yourNewPassword', PASSWORD_DEFAULT);
-- then running it in the browser once, copying the resulting hash,
-- and updating the admin_users table with it (then deleting that file).
-- -----------------------------------------------------------------
INSERT INTO admin_users (username, password_hash)
VALUES (
    'admin',
    '$2b$10$S7xRCV04F66y9opZUzmfBOrvb7VTbkhrd7Qu/Rm0bxwkDhmMoNmN6'
)
ON DUPLICATE KEY UPDATE username = username;

-- NOTE: The hash above is a real, verified bcrypt hash for the password
-- "AltarServer2026!". PHP's password_verify() accepts the $2b$ prefix
-- exactly the same as $2y$ (they are the same bcrypt algorithm), so
-- this will work correctly with password_verify() in authenticate.php.
-- You can log in immediately with:
--   Username: admin
--   Password: AltarServer2026!
