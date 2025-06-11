<?php
// --- Unique session name per subdomain ---
$subdomain = explode('.', $_SERVER['HTTP_HOST'])[0] ?? 'default';
session_name($subdomain . '_session');

// --- Secure cookie settings ---
session_set_cookie_params([
    'lifetime' => 0, // until browser closes
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'], // don't force .domain.com unless needed
    'secure' => isset($_SERVER['HTTPS']), // true only if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax', // good default: avoids CSRF, works with most forms
]);

session_start();

// --- Optional: regenerate session ID after login
// Use this right after successful login
// session_regenerate_id(true);

// --- SQLite DB setup (optional) ---
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/data/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// $pdo is now ready to use globally