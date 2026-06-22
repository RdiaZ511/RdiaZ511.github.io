<?php
session_start();                     // Inicia sesión (aunque aún no estemos logueados)
$logFile = __DIR__ . '/login_log.txt';

// Cargar usuarios desde auth.json
$authPath = __DIR__ . '/auth.json';
$users = [];
if (file_exists($authPath)) {
    $json = file_get_contents($authPath);
    $data = json_decode($json, true);
    $users = $data['users'] ?? [];
}

// Función para registrar un intento de login
function logAttempt(string $status, string $username, string $ip): void
{
    global $logFile;
    $now = date('Y-m-d H:i:s');
    $line = "[$now] $status - User: $username - IP: $ip\n";
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

// Procesar envío del formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validación básica
    if ($username === '' || $password === '') {
        $error = 'Usuario y contraseña son obligatorios';
    } else {
        // Verificar credenciales
        if (isset($users[$username]) && password_verify($password, $users[$username])) {
            // Login exitoso
            $_SESSION['logged_in'] = true;
            $_SESSION['username']   = $username;   // opcional, para usar después
            logAttempt('SUCCESS', $username, $_SERVER['REMOTE_ADDR']);
            // Redirigir a assets.php (el panel)
            header('Location: assets.php');
            exit;
        } else {
            // Login fallido
            logAttempt('FAIL', $username, $_SERVER['REMOTE_ADDR']);
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login – Gestión de Unidades Economicas</title>
    <!-- Bootstrap 5.3 (usa la misma ruta que tenías en assets.php) -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-box {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: #fff;
            border-radius: .5rem;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="bg-light">

<div class="login-box">
    <h2 class="mb-4">Iniciar sesión</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <div class="mb-3">
            <label for="username" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="username" name="username" required autofocus>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
</div>

<!-- Bootstrap bundle (incluye Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-p3N2GG4n6x5b4c2Zc8K8H4F9bKc9cZ5K6Xc3jR7j2Q6X8j4Vx5p9eQ5Zz5v5eK5"
        crossorigin="anonymous"></script>
</body>
</html>