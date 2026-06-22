<?php
session_start();
require_once 'auth_check.php';
if (!$_SESSION['logged_in']) {
    header('Location: index.php');
    exit;
}

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
// 2️⃣  LÓGICA DE BÚSQUEDA Y FETCH DE ENTIDADES
// -------------------------------------------------
class Entidad {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }
    
    public function fetchAll() {
        try {
            // Se cargan TODAS las entidades para filtrar en PHP si es necesario
            // O se podría hacer la búsqueda directamente en SQL con WHERE
            return $this->pdo->query('SELECT * FROM entidad')->fetchAll();
        } catch (PDOException $e) {
            echo "Error al leer la tabla: " . $e->getMessage();
            return [];
        }
    }
}
$entities = (new Entidad($pdo))->fetchAll();

// -------------------------------------------------
// 3️⃣  RENDERIZADO DE BÚSQUEDA Y FORMULARIO
// -------------------------------------------------
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Gestión de Unidades Economicas – Estado Carabobo</title>
        <!-- Bootstrap 5.3 -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .card {
                margin-bottom: 1rem;
            }
            .table-responsive .table {
                table-layout: fixed;
            }

            /* Estilos dinámicos para la tabla según el estado de búsqueda */
            .table-match {
                background-color: #d1e7dd !important;
                /* Verde claro */
            }
            .table-nomatch {
                background-color: #f8d7da !important;
                /* Rojo claro */
            }
            .table-nomatch tbody td {
                color: #721c24;
            }
        </style>
    </head>
    <body class="bg-light">
        <div class="container py-4">
            <h1 class="mb-4 text-center">Gestión de Unidades Economicas del Estado Carabobo</h1>

            <div class="row mb-3">
                <div class="col">
                    <!-- Formulario de búsqueda -->
                    <form action="assets.php" method="GET" class="d-flex gap-2">
                        <input
                            type="text"
                            name="q"
                            class="form-control form-control-lg w-75"
                            placeholder="Buscar por código o entidad..."
                            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <button type="submit" class="btn btn-primary">
                            Buscar
                        </button>
                        <!-- Opción para limpiar búsqueda (opcional, visual) -->
                        <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                        <a href="assets.php" class="btn btn-outline-secondary">Limpiar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <a href="nuevo.php" class="btn btn-primary w-100 mb-3">📄
                        <strong>Agregar</strong>
                    </a>

                    <div class="alert alert-info">
                        Total 
                        <strong><?= count($entities) ?></strong>
                        registro(s).
                        <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                        Se ha realizado un filtrado por:
                        <span class="text-dark">"<?= htmlspecialchars($_GET['q']) ?>"</span>
                    <?php else: ?>
                        Sin filtros.
                        <?php endif; ?>
                    </div>

                    <!-- ----------------------------------------------------------------- Tabla de
                    entidades (responsive, striped, hover, dark header)
                    ----------------------------------------------------------------- -->
                    <div class="table-responsive">
                        <!-- Se añade la clase dinámica según si hay búsqueda y resultados -->
                        <table
                            class="table table-striped table-hover align-middle";>
                            <thead class="table-primary">
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Entidad</th>
                                    <th scope="col">Fecha de inicio</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                                // Si hay búsqueda y no hay resultados coincidentes, no mostrar nada
                                if (isset($_GET['q']) && !empty($_GET['q'])) {
                                    $busqueda = strtoupper($_GET['q']);
                                    $hayResultados = false;

                                    foreach ($entities as $e) {
                                        // Verificar coincidencia en Entidad
                                        if (!($e['entidad'] === "" || $e['entidad'] == null)) {
                                            if (stripos($e['entidad'], $busqueda) !== false) {
                                                $hayResultados = true;
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($e['codigo']) . "</td>";
                                                echo "<td>" . htmlspecialchars($e['entidad']) . "</td>";
                                                echo "<td>" . htmlspecialchars($e['fecha_inicio']) . "</td>";
                                                echo "<td>" . htmlspecialchars(($e['operativa'] == 1 ? 'Operativa' : 'Inactiva')) . "</td>";
                                                echo "<td class='d-flex gap-2'>";
                                                echo "<a href='editar.php?id=" . htmlspecialchars($e['codigo']) . "' class='btn btn-sm btn-outline-primary'>Editar</a>";
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                        }
                                    }

                                    // Si el bucle no encontró nada, mostramos mensaje
                                    if (!$hayResultados) {
                                        echo '<tr><td colspan="5" class="text-center text-danger p-4">❌ No se encontraron開coincidencias para: "' . htmlspecialchars($_GET['q']) . '"</td></tr>';
                                    }

                                } else {
                                    // Si NO hay búsqueda, mostramos TODOS los registros sin filtrar
                                    foreach ($entities as $e) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($e['codigo']) . "</td>";
                                        echo "<td>" . htmlspecialchars($e['entidad']) . "</td>";
                                        echo "<td>" . htmlspecialchars($e['fecha_inicio']) . "</td>";
                                        echo "<td>" . htmlspecialchars(($e['operativa'] == 1 ? 'Operativa' : 'Inactiva')) . "</td>";
                                        echo "<td class='d-flex gap-2'>";
                                        echo "<a href='editar.php?id=" . htmlspecialchars($e['codigo']) . "' class='btn btn-sm btn-outline-primary'>Editar</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (count($entities) == 0 && !isset($_GET['q'])): ?>
            <div class="text-center py-5">
                <h4>No hay entidades registradas.</h4>
            </div>
            <?php endif; ?>

        </div>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/jquery-4.0.0.min.js"></script>
    </body>
</html>