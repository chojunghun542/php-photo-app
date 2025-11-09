<?php

declare(strict_types=1);
// app/bootstrap.php - session and security headers
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'secure' => true,    // require HTTPS in production
        'httponly' => true,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}
// Security headers (also set at webserver if possible)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
