<?php
/**
 * config/database.php
 * -----------------------------------------------------------------
 * This file is the ONLY place where the database connection settings
 * live. Every other PHP file "includes" this file whenever it needs
 * to talk to the database. That way, if you move the project from
 * XAMPP (local) to an online host, you only need to change the
 * values in ONE file instead of hunting through the whole project.
 * -----------------------------------------------------------------
 */

// ---------------------------------------------------------------
// LOCAL (XAMPP) SETTINGS
// When you deploy online, just change these 4 values to the ones
// given to you by your hosting provider (see the README section
// "Moving to Online Hosting").
// ---------------------------------------------------------------
$DB_HOST = "localhost";
$DB_NAME = "altar_server_feedback";
$DB_USER = "root";
$DB_PASS = "";

// Turn this to false once the site is live on real hosting.
// When true, PHP errors are shown (helpful while developing).
// When false, PHP errors are hidden from visitors (safer).
define('APP_DEBUG', true);

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            // Throw exceptions on errors instead of silently failing
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Return rows as associative arrays, e.g. $row['name']
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Use real prepared statements (safer against SQL injection)
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    if (APP_DEBUG) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("We're having trouble connecting right now. Please try again later.");
    }
}

// From here on, any file that does `require_once 'config/database.php';`
// can simply use the $pdo variable to run queries.
