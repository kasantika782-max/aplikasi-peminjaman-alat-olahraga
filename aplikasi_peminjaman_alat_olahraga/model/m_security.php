<?php
// Helper keamanan aplikasi: CSRF + validasi request.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Metode request tidak diizinkan.');
    }
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Permintaan ditolak: token keamanan tidak valid. Silakan muat ulang halaman.');
    }
}

function require_login(): void {
    if (!isset($_SESSION['id_user'], $_SESSION['role'])) {
        header('Location: ../view/v_login.php');
        exit();
    }
}
