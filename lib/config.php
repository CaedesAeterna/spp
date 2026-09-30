<?php
declare(strict_types=1);

/**
 * Settings for the whole application.
 * Change the MySQL user/password here if yours are different.
 */

const APP_NAME = 'SPP homework';

// --- MySQL (XAMPP defaults: user "root", empty password) -----------------
const DB_HOST    = '127.0.0.1';   // 127.0.0.1 forces TCP; "localhost" uses the unix socket
const DB_PORT    = 3306;
const DB_NAME    = 'spp';
const DB_USER    = 'root';
const DB_PASS    = '';
const DB_CHARSET = 'utf8mb4';

// The database is created and filled from this file on the first request.
define('DB_SCHEMA_FILE', __DIR__ . '/../sql/schema.sql');
const DB_MARKER_TABLE = 'users';   // when this table is missing, the schema is run

// --- Task 1: a user/password pair written into the code ------------------
// login.php accepts this pair even when MySQL is empty, so the login form can
// be demonstrated without the database.
const BUILTIN_USERNAME = 'admin';
const BUILTIN_PASSWORD = 'titok123';

// --- Task 3/4: the guessing game ----------------------------------------
const SECRET_LENGTH = 5;   // the number has 5 digits, all different (at most 10)

// While presenting: show the secret to an administrator on the game page, so
// the two kinds of feedback can be demonstrated on purpose instead of by luck.
// Set it to false and nobody sees the number before guessing it.
const SHOW_SECRET_TO_ADMIN = true;

ini_set('display_errors', '1');
error_reporting(E_ALL);
