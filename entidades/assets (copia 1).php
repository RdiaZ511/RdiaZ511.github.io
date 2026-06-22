<?php
/**
 * index.php
 * -------------------------------------------------
 * 1️⃣  Carga la configuración de la BD (db.php)
 * 2️⃣  Obtiene todas las filas de la tabla `entidad`
 * 3️⃣  Renderiza una tablaBootstrap‑styled con 
 *     botones “Editar” y “Eliminar” (con confirmación)
 * -------------------------------------------------
 */

 // -------------------------------------------------
 // 1️⃣  CONFIGURACIÓN DE CONEXIÓN (db.php)
 // -------------------------------------------------
$connectionData = require 'db.php';
if (isset($connectionData['error'])) {
    echo "❌  $connectionData[message]";
    exit;
}

$pdo = new PDO(
    $connectionData['dsn'],
    $connectionData['user'],
    $connectionData['pass'],
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);

// -------------------------------------------------
 // 2️⃣  FETCH DE TODAS LAS ENTIDADES
 // -------------------------------------------------
class Entidad {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }
    public function fetchAll() {
        try {
            return $this->pdo->query('SELECT * FROM entidad')->fetchAll();
        } catch (PDOException $e) {
            echo "Error al leer la tabla: " . $e->getMessage();
            return [];
        }
    }
}
$entities = (new Entidad($pdo))->fetchAll();   // <--  <--  <--  <--  <--  <--  [ENTIRE RESULT SET]

// -------------------------------------------------
 // 3️⃣  RENDER HTML (Bootstrap 5.3)
 // -------------------------------------------------
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Entidades – Estado Carabobo</title>
    <!-- Bootstrap 5.3 -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {margin-bottom:1rem;}
        .card-body {class:table-responsive;}
        .action-buttons {display:flex; gap:0.5rem; flex-wrap:wrap;}
    </style>
</head>

<body class="bg-light">

<div class="container py-4">

    <h1 class="mb-4 text-center">Gestión de Entidades – Carabobo</h1>
    <div class="row">
        <div class="col">
            <a href="nuevo.php" class="btn btn-primary w-100">📄  <strong>Agregar</strong></a>
        </div>
    </div>

    <!-- -----------------------------------------------------------------
         Tabla de entidades (responsive, striped, hover, dark header)
      ----------------------------------------------------------------- -->
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Entidad</th>
                    <th scope="col">Fecha de inicio</th>
                    <th scope="col">Telefono</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entities)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay entidades registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entities as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['codigo']) ?></td>
                            <td><?= htmlspecialchars($e['entidad']) ?></td>
                            <td><?= htmlspecialchars($e['fecha_inicio']) ?></td>
                            <td>
                                <?php
                                // Mostrar un ícono de estado (operativa / inactiva)
                                $status = $e['instalada'] == 1 || $e['operativa'] == 1 ? 'Operativa' : 'Inactiva';
                                echo htmlspecialchars($status);
                                ?>
                            </td>
                            <td class="d-flex gap-2">
                                <!-- EDITAR -->
                                <a href="editar.php?id=<?= $e['codigo']; ?>"
                                   class="btn btn-sm btn-outline-success">🖊️ Editar</a>

                                <!-- ELIMINAR -->
                                <form action="index.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="codigo_original" value="<?= $e['codigo']; ?>">
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Estás seguro de eliminar este registro?')">
                                        🗑️ Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/jquery-4.0.0.min.js"></script>
</body>
</html>