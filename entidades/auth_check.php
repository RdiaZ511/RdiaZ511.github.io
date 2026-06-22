<?php
// auth_check.php – incluye la validación de sesión

// Inicia la sesión (si no lo está ya)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión iniciada, el propio assets.php la redirigirá, pero por seguridad:
if (empty($_SESSION['logged_in']) || empty($_SESSION['username'])) {
    // No está autenticado → dejar que assets.php lo maneje (el header allí)
    return;
}
?>