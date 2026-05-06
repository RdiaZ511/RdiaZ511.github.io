Aquí tienes la planificación detallada para tu sistema de control de insumos. He estructurado el documento para que sirva como hoja de ruta técnica y de diseño.

Puedes copiar el siguiente contenido y guardarlo como `planificacion_inventario.md`.

***

# 📦 Planificación de Sistema de Control de Insumos (SCI)

## 1. Descripción General
El sistema tiene como objetivo gestionar el stock de insumos mediante dos operaciones fundamentales: **Cargas** (entrada de material) y **Entregas** (salida de material). A diferencia de un sistema comercial, no existen procesos de compra o venta, sino movimientos internos de almacén.

### Reglas de Negocio Críticas:
*   **Cargas:** Permiten la repetición del número de documento (ej. varias cargas pueden venir bajo una misma guía de remisión general).
*   **Entregas:** El número de documento debe ser **único** (para evitar duplicidad en la salida de materiales y asegurar la trazabilidad).
*   **Stock:** El saldo actual de un insumo es la suma de todas las Cargas menos la suma de todas las Entregas.

---

## 2. Arquitectura Técnica
*   **Lenguaje:** PHP (Versión 8.x recomendada).
*   **Base de Datos:** MySQL.
*   **Frontend:** HTML5, CSS3 (Bootstrap 5 para agilizar el UI/UX) y JavaScript.
*   **Reportes:** Generación de tablas HTML para vista previa y librerías como `DomPDF` o `TCPDF` para impresiones en PDF.

---

## 3. Diseño de la Base de Datos (Modelo Relacional)

### Diagrama de Tablas

#### Tabla: `categorias`
Organiza los insumos por grupos.
*   `id_categoria` (INT, PK, AI)
*   `nombre` (VARCHAR 100)
*   `descripcion` (TEXT)

#### Tabla: `insumos`
El catálogo de materiales.
*   `id_insumo` (INT, PK, AI)
*   `codigo` (VARCHAR 50, UNIQUE) - Código interno del material.
*   `nombre` (VARCHAR 150)
*   `unidad_medida` (VARCHAR 20) - Ej: Unidades, Litros, Metros.
*   `stock_minimo` (DECIMAL 10,2) - Para alertas de reabastecimiento.
*   `id_categoria` (INT, FK)

#### Tabla: `movimientos`
Tabla central donde se registran tanto Cargas como Entregas.
*   `id_movimiento` (INT, PK, AI)
*   `tipo` (ENUM('Carga', 'Entrega'))
*   `num_documento` (VARCHAR 50) - *Nota: No se pone UNIQUE aquí porque la Carga lo permite.*
*   `fecha` (DATETIME)
*   `observaciones` (TEXT)
*   `usuario_id` (INT, FK) - Quien realizó la operación.

#### Tabla: `detalle_movimientos`
Desglose de los insumos afectados en cada movimiento.
*   `id_detalle` (INT, PK, AI)
*   `id_movimiento` (INT, FK)
*   `id_insumo` (INT, FK)
*   `cantidad` (DECIMAL 10,2)

#### Tabla: `usuarios`
Gestión de acceso.
*   `id_usuario` (INT, PK, AI)
*   `username` (VARCHAR 50, UNIQUE)
*   `password` (VARCHAR 255)
*   `rol` (ENUM('Admin', 'Operador'))

### Restricción de Documento Único (Lógica de Negocio)
Para cumplir la regla de que el `num_documento` de **Entrega** no se repita, se implementará una validación en el Backend (PHP) antes de insertar:
`SELECT COUNT(*) FROM movimientos WHERE tipo = 'Entrega' AND num_documento = 'VALOR_INGRESADO'`

---

## 4. Planificación de UI/UX

### Mapa del Sitio (Navegación)
1.  **Dashboard:** Resumen visual (Insumos críticos, últimas 5 operaciones).
2.  **Gestión de Insumos:**
    *   Lista de insumos y stock actual.
    *   Formulario de alta/edición de insumos.
3.  **Movimientos:**
    *   **Módulo de Carga:** Formulario para ingresar documento, fecha y selección de múltiples insumos con cantidad.
    *   **Módulo de Entrega:** Similar a la carga, con validación de stock disponible y validación de documento único.
4.  **Reportes:**
    *   Reporte de Inventario General (Stock actual).
    *   Historial de Movimientos (Filtro por fecha, tipo e insumo).
5.  **Impresiones:**
    *   Comprobante de Carga.
    *   Vale de Entrega.

### Guía de Estilo Visual
*   **Colores:** 
    *   Primario: Azul Industrial (`#2c3e50`) para profesionalismo.
    *   Carga: Verde (`#27ae60`) para representar incremento.
    *   Entrega: Rojo/Naranja (`#e67e22`) para representar decremento.
*   **Componentes:** 
    *   Uso de **Modales** para agregar insumos rápidamente en una carga/entrega.
    *   **Tablas Responsivas** con búsqueda instantánea (DataTables).
    *   **Alertas (Toasts)** para confirmar que el documento de entrega no estaba duplicado.

---

## 5. Flujo de Trabajo de Reportes e Impresiones

### A. Reporte de Inventario
*   **Consulta:** `SELECT i.nombre, SUM(CASE WHEN m.tipo='Carga' THEN d.cantidad ELSE -d.cantidad END) as stock_actual FROM insumos i ...`
*   **Visualización:** Tabla con resaltado en rojo si `stock_actual` < `stock_minimo`.

### B. Impresiones de Documentos
Se diseñarán dos plantillas limpias (estilo factura/recibo) que contengan:
1.  **Encabezado:** Logo de la empresa, Título (Vale de Entrega / Recibo de Carga).
2.  **Datos del Documento:** Número de documento, Fecha, Operador.
3.  **Tabla de Insumos:** Código, Nombre, Cantidad, Unidad de Medida.
4.  **Pie de página:** Espacio para firma de quien entrega y quien recibe.

---

## 6. Cronograma de Implementación Sugerido

| Fase | Tarea | Detalle |
| :--- | :--- | :--- |
| **1** | Base de Datos | Creación de tablas y relaciones en MySQL. |
| **2** | Core PHP | Conexión BD y CRUD básico de Insumos y Categorías. |
| **3** | Lógica de Movimientos | Desarrollo de Cargas y Entregas (con validaciones). |
| **4** | UI/UX | Aplicación de Bootstrap y diseño de vistas. |
| **5** | Reportes e Impresiones | Implementación de filtros y generación de PDF. |
| **6** | Pruebas | Test de estrés de documentos duplicados y cálculo de stock. |




DROP DATABASE IF EXISTS sistema_insumos;
CREATE DATABASE sistema_insumos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_insumos;

-- 1. Tablas Maestras
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE insumos (
    id_insumo INT AUTO_INCREMENT PRIMARY KEY,
    codigo_interno VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    id_categoria INT,
    stock_actual DECIMAL(10,2) DEFAULT 0.00,
    stock_minimo DECIMAL(10,2) DEFAULT 0.00,
    unidad_medida VARCHAR(20) DEFAULT 'Unidades',
    CONSTRAINT fk_insumo_cat FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('Admin', 'Operador') DEFAULT 'Operador'
) ENGINE=InnoDB;

CREATE TABLE secuenciador_documentos (
    nombre_secuencia VARCHAR(20) PRIMARY KEY,
    ultimo_valor INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO secuenciador_documentos VALUES ('ENTREGAS', 0);

-- 2. Tabla Maestro (El Trámite)
CREATE TABLE movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('Carga', 'Entrega') NOT NULL,
    num_documento VARCHAR(50), 
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    responsable_entrega VARCHAR(100), -- Quién da el material
    responsable_recibe VARCHAR(100),  -- Quién recibe el material
    observaciones TEXT,
    CONSTRAINT fk_mov_user FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- 3. Tabla Detalle (La lista de insumos)
CREATE TABLE detalle_movimientos (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_movimiento INT NOT NULL,
    id_insumo INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_det_mov FOREIGN KEY (id_movimiento) REFERENCES movimientos(id_movimiento) ON DELETE CASCADE,
    CONSTRAINT fk_det_insumo FOREIGN KEY (id_insumo) REFERENCES insumos(id_insumo)
) ENGINE=InnoDB;

-- =============================================================================
-- LÓGICA DE AUTOMATIZACIÓN
-- =============================================================================

DELIMITER //

-- Trigger para generar el número de documento auto en Entregas
CREATE TRIGGER tr_gen_doc_movimiento BEFORE INSERT ON movimientos
FOR EACH ROW
BEGIN
    IF NEW.tipo = 'Entrega' THEN
        UPDATE secuenciador_documentos SET ultimo_valor = ultimo_valor + 1 WHERE nombre_secuencia = 'ENTREGAS';
        SET NEW.num_documento = CONCAT('ENT-', LPAD((SELECT ultimo_valor FROM secuenciador_documentos WHERE nombre_secuencia = 'ENTREGAS'), 6, '0'));
    END IF;
END //

-- Trigger para actualizar el stock basándose en los DETALLES
-- Importante: Ahora el trigger actúa sobre la tabla DETALLE, no sobre la de movimientos
CREATE TRIGGER tr_actualizar_stock_detalle AFTER INSERT ON detalle_movimientos
FOR EACH ROW
BEGIN
    DECLARE v_tipo ENUM('Carga', 'Entrega');
    
    -- Buscamos el tipo de movimiento al que pertenece este detalle
    SELECT tipo INTO v_tipo FROM movimientos WHERE id_movimiento = NEW.id_movimiento;
    
    IF v_tipo = 'Carga' THEN
        UPDATE insumos SET stock_actual = stock_actual + NEW.cantidad WHERE id_insumo = NEW.id_insumo;
    ELSEIF v_tipo = 'Entrega' THEN
        UPDATE insumos SET stock_actual = stock_actual - NEW.cantidad WHERE id_insumo = NEW.id_insumo;
    END IF;
END //

DELIMITER ;



-- =============================================================================
-- ARQUITECTURA DE CARPETAS
-- =============================================================================
/sistema_insumos
│
├── /assets              <-- RECURSOS LOCALES (No CDN)
│   ├── /css
│   │   └── bootstrap.min.css
│   ├── /js
│   │   ├── bootstrap.bundle.min.js
│   │   └── jquery.min.js
│   └── /img
│
├── /config
│   └── db.php           <-- Conexión a MySQL
│
├── /includes
│   ├── funciones.php    <-- Lógica de negocio (Cargar/Descargar)
│   └── header.php       <-- Menú y Navbar
│
├── /modulos
│   ├── insumos.php      <-- Gestión de catálogo
│   ├── carga.php        <-- Formulario de Entrada (Maestro-Detalle)
│   ├── entrega.php      <-- Formulario de Salida (Maestro-Detalle)
│   └── reportes.php     <-- Listados e Impresiones
│
└── index.php            <-- Dashboard principal



# ========================================================================================
# CONTENEDOR DE PROGRAMACIÓN: SISTEMA DE CONTROL DE INSUMOS (SCI)
# ARQUITECTURA: PHP Procedural Organizado | UI: Bootstrap 5 (Local) | DB: MySQL
# ========================================================================================

# ----------------------------------------------------------------------------------------
# ARCHIVO: /config/db.php
# FUNCIÓN: Establece la conexión con la base de datos MySQL y define el charset.
# ----------------------------------------------------------------------------------------
<?php
$host = "localhost";
$user = "root"; 
$pass = "";     
$db   = "sistema_insumos";

$conexion = mysqli_connect($host, $user, $pass, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conexion, "utf8");
?>


# ----------------------------------------------------------------------------------------
# ARCHIVO: /includes/header.php
# FUNCIÓN: Define la estructura HTML, carga de CSS locales y la barra de navegación lateral.
# ----------------------------------------------------------------------------------------
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCI - Control de Insumos</title>
    <!-- RECURSOS LOCALES (Asegurarse de descargar Bootstrap 5 y colocarlo en /assets) -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Arial, sans-serif; }
        .navbar-custom { background-color: #2c3e50 !important; }
        .sidebar { min-height: 100vh; background: #34495e; color: white; transition: all 0.3s; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 15px 20px; display: block; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background: #2c3e50; color: white; }
        .sidebar .active { background: #1abc9c; color: white; font-weight: bold; }
        .card-executivo { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .bg-carga { background-color: #27ae60 !important; color: white; }
        .bg-entrega { background-color: #e74c3c !important; color: white; }
        .btn-exec { background-color: #2c3e50; color: white; }
        .btn-exec:hover { background-color: #1a252f; color: white; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block sidebar shadow">
            <div class="text-center py-4">
                <h4 class="fw-bold">SCI v1.0</h4>
                <small class="text-muted">Control de Insumos</small>
            </div>
            <a href="/index.php" class="active">📊 Dashboard</a>
            <a href="/modulos/insumos.php">📦 Catálogo Insumos</a>
            <a href="/modulos/carga.php">📥 Nueva Carga</a>
            <a href="/modulos/entrega.php">📤 Nueva Entrega</a>
            <a href="/modulos/reportes.php">📄 Reportes e Impresiones</a>
        </nav>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">


# ----------------------------------------------------------------------------------------
# ARCHIVO: /includes/footer.php
# FUNCIÓN: Cierra etiquetas HTML y carga los scripts de jQuery y Bootstrap desde local.
# ----------------------------------------------------------------------------------------
        </main>
    </div>
</div>
<!-- RECURSOS LOCALES -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>


# ----------------------------------------------------------------------------------------
# ARCHIVO: /index.php
# FUNCIÓN: Panel principal con indicadores rápidos (Widgets) de estado del inventario.
# ----------------------------------------------------------------------------------------
<?php 
include 'config/db.php'; 
include 'includes/header.php'; 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Resumen Ejecutivo</h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-executivo text-white bg-primary mb-4">
            <div class="card-body text-center">
                <h6>Total de Insumos</h6>
                <?php 
                $res = mysqli_query($conexion, "SELECT COUNT(*) as total FROM insumos");
                $data = mysqli_fetch_assoc($res);
                echo "<h2>".$data['total']."</h2>";
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-executivo text-white bg-danger mb-4">
            <div class="card-body text-center">
                <h6>Stock Crítico (Bajo)</h6>
                <?php 
                $res = mysqli_query($conexion, "SELECT COUNT(*) as total FROM insumos WHERE stock_actual <= stock_minimo");
                $data = mysqli_fetch_assoc($res);
                echo "<h2>".$data['total']."</h2>";
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-executivo text-white bg-success mb-4">
            <div class="card-body text-center">
                <h6>Movimientos Hoy</h6>
                <?php 
                $res = mysqli_query($conexion, "SELECT COUNT(*) as total FROM movimientos WHERE DATE(fecha_hora) = CURDATE()");
                $data = mysqli_fetch_assoc($res);
                echo "<h2>".$data['total']."</h2>";
                ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


# ----------------------------------------------------------------------------------------
# ARCHIVO: /modulos/insumos.php
# FUNCIÓN: Gestión del catálogo (CRUD). Permite agregar insumos y ver el stock actual.
# ----------------------------------------------------------------------------------------
<?php 
include '../config/db.php'; 
include '../includes/header.php'; 

if(isset($_POST['guardar'])){
    $cod = $_POST['codigo']; $nom = $_POST['nombre']; $cat = $_POST['categoria']; $min = $_POST['minimo']; $uni = $_POST['unidad'];
    mysqli_query($conexion, "INSERT INTO insumos (codigo_interno, nombre, id_categoria, stock_minimo, unidad_medida) VALUES ('$cod', '$nom', '$cat', '$min', '$uni')");
}
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h2>📦 Catálogo de Insumos</h2>
    <button class="btn btn-exec" data-bs-toggle="modal" data-bs-target="#modalInsumo">+ Nuevo Insumo</button>
</div>

<div class="card card-executivo bg-white">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Stock Actual</th>
                    <th>Mínimo</th>
                    <th>Unidad</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = mysqli_query($conexion, "SELECT * FROM insumos");
                while($row = mysqli_fetch_assoc($res)){
                    $est = ($row['stock_actual'] <= $row['stock_minimo']) ? '<span class="badge bg-danger">Bajo</span>' : '<span class="badge bg-success">OK</span>';
                    echo "<tr>
                        <td>{$row['codigo_interno']}</td>
                        <td>{$row['nombre']}</td>
                        <td class='fw-bold'>{$row['stock_actual']}</td>
                        <td>{$row['stock_minimo']}</td>
                        <td>{$row['unidad_medida']}</td>
                        <td>$est</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="modalInsumo" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Agregar Insumo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Código Interno</label><input type="text" name="codigo" class="form-control" required></div>
                <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                <div class="mb-3"><label>Categoría (ID)</label><input type="number" name="categoria" class="form-control"></div>
                <div class="mb-3"><label>Stock Mínimo</label><input type="number" step="0.01" name="minimo" class="form-control" required></div>
                <div class="mb-3"><label>Unidad de Medida</label><input type="text" name="unidad" class="form-control" placeholder="Ej: Litros, Unidades"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="guardar" class="btn btn-exec">Guardar Insumo</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


# ----------------------------------------------------------------------------------------
# ARCHIVO: /modulos/carga.php (Y Lógica similar para entrega.php)
# FUNCIÓN: Proceso Maestro-Detalle. Registra el trámite y una lista dinámica de insumos.
# NOTA: Para entrega.php, cambiar 'Carga' por 'Entrega' y ocultar campo 'Num Documento'.
# ----------------------------------------------------------------------------------------
<?php 
include '../config/db.php'; 
include '../includes/header.php'; 

if(isset($_POST['procesar'])){
    $tipo = 'Carga';
    $doc = $_POST['num_documento'];
    $ent = $_POST['resp_entrega'];
    $rec = $_POST['resp_recibe'];
    $obs = $_POST['observaciones'];
    $uid = 1; // Simulación de usuario logueado

    mysqli_begin_transaction($conexion);
    try {
        $sql_m = "INSERT INTO movimientos (tipo, num_documento, responsable_entrega, responsable_recibe, observaciones, usuario_id) 
                       VALUES ('$tipo', '$doc', '$ent', '$rec', '$obs', '$uid')";
        mysqli_query($conexion, $sql_m);
        $id_mov = mysqli_insert_id($conexion);

        foreach($_POST['insumo_id'] as $index => $id_insumo){
            $cant = $_POST['cantidad'][$index];
            mysqli_query($conexion, "INSERT INTO detalle_movimientos (id_movimiento, id_insumo, cantidad) VALUES ('$id_mov', '$id_insumo', '$cant')");
        }
        mysqli_commit($conexion);
        echo "<div class='alert alert-success'>Trámite de Carga registrado exitosamente.</div>";
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        echo "<div class='alert alert-danger'>Error en el proceso.</div>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h2>📥 Nueva Carga de Insumos</h2>
</div>

<form method="POST">
    <div class="card card-executivo mb-4">
        <div class="card-header bg-carga"><h5>Datos del Trámite</h5></div>
        <div class="card-body row">
            <div class="col-md-3 mb-3"><label>Número Documento</label><input type="text" name="num_documento" class="form-control" required></div>
            <div class="col-md-3 mb-3"><label>Responsable Entrega</label><input type="text" name="resp_entrega" class="form-control" required></div>
            <div class="col-md-3 mb-3"><label>Responsable Recibe</label><input type="text" name="resp_recibe" class="form-control" required></div>
            <div class="col-md-3 mb-3"><label>Observaciones</label><input type="text" name="observaciones" class="form-control"></div>
        </div>
    </div>

    <div class="card card-executivo mb-4">
        <div class="card-header bg-dark text-white"><h5>Lista de Materiales</h5></div>
        <div class="card-body">
            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-5">
                    <label>Seleccione Insumo</label>
                    <select id="sel_insumo" class="form-select">
                        <?php 
                        $res = mysqli_query($conexion, "SELECT id_insumo, nombre FROM insumos");
                        while($row = mysqli_fetch_assoc($res)) echo "<option value='{$row['id_insumo']}'>{$row['nombre']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Cantidad</label>
                    <input type="number" id="sel_cantidad" class="form-control" step="0.01">
                </div>
                <div class="col-md-4">
                    <button type="button" id="btn_agregar" class="btn btn-exec w-100">➕ Agregar a la Lista</button>
                </div>
            </div>

            <table class="table table-bordered" id="tabla_detalles">
                <thead class="table-light">
                    <tr><th>Insumo</th><th>Cantidad</th><th>Acción</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <button type="submit" name="procesar" class="btn btn-lg btn-success w-100 shadow">💾 Finalizar y Guardar Carga</button>
</form>

<script>
$(document).ready(function(){
    $("#btn_agregar").click(function(){
        let nombre = $("#sel_insumo option:selected").text();
        let id = $("#sel_insumo").val();
        let cant = $("#sel_cantidad").val();
        if(cant == "" || cant <= 0) return alert("Ingrese cantidad válida");

        let fila = `<tr>
            <td>${nombre} <input type="hidden" name="insumo_id[]" value="${id}"></td>
            <td>${cant} <input type="hidden" name="cantidad[]" value="${cant}"></td>
            <td><button type="button" class="btn btn-sm btn-danger btn_eliminar">Eliminar</button></td>
        </tr>`;
        $("#tabla_detalles tbody").append(fila);
        $("#sel_cantidad").val("");
    });

    $(document).on('click', '.btn_eliminar', function(){
        $(this).closest('tr').remove();
    });
});
</script>

<?php include '../includes/footer.php'; ?>


# --------------------------------------------------------------------------------------------------------
# ARCHIVO: /modulos/entrega.php
# FUNCIÓN: Idéntico a carga.php pero con tipo 'Entrega'. 
# CAMBIOS CLAVE: 1. $tipo = 'Entrega'; 2. Quitar campo 'num_documento' (es automático por trigger).
# ----------------------------------------------------------------------------------------
<?php 
include '../config/db.php'; 
include '../includes/header.php'; 

if(isset($_POST['procesar'])){
    $tipo = 'Entrega'; // Cambio fundamental
    $ent = $_POST['resp_entrega'];
    $rec = $_POST['resp_recibe'];
    $obs = $_POST['observaciones'];
    $uid = 1;

    mysqli_begin_transaction($conexion);
    try {
        // Documento se genera solo mediante el Trigger de MySQL
        $sql_m = "INSERT INTO movimientos (tipo, responsable_entrega, responsable_recibe, observaciones, usuario_id) 
                   VALUES ('$tipo', '$ent', '$rec', '$obs', '$uid')";
        mysqli_query($conexion, $sql_m);
        $id_mov = mysqli_insert_id($conexion);

        foreach($_POST['insumo_id'] as $index => $id_insumo){
            $cant = $_POST['cantidad'][$index];
            mysqli_query($conexion, "INSERT INTO detalle_movimientos (id_movimiento, id_insumo, cantidad) VALUES ('$id_mov', '$id_insumo', '$cant')");
        }
        mysqli_commit($conexion);
        echo "<div class='alert alert-success'>Vale de Entrega generado correctamente.</div>";
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        echo "<div class='alert alert-danger'>Error en el proceso.</div>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h2>📤 Nueva Entrega de Insumos</h2>
</div>

<form method="POST">
    <div class="card card-executivo mb-4">
        <div class="card-header bg-entrega"><h5>Datos del Trámite (Documento Automático)</h5></div>
        <div class="card-body row">
            <div class="col-md-4 mb-3"><label>Responsable Entrega</label><input type="text" name="resp_entrega" class="form-control" required></div>
            <div class="col-md-4 mb-3"><label>Responsable Recibe</label><input type="text" name="resp_recibe" class="form-control" required></div>
            <div class="col-md-4 mb-3"><label>Observaciones</label><input type="text" name="observaciones" class="form-control"></div>
        </div>
    </div>

    <div class="card card-executivo mb-4">
        <div class="card-header bg-dark text-white"><h5>Lista de Materiales</h5></div>
        <div class="card-body">
            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-5">
                    <label>Seleccione Insumo</label>
                    <select id="sel_insumo" class="form-select">
                        <?php 
                        $res = mysqli_query($conexion, "SELECT id_insumo, nombre FROM insumos");
                        while($row = mysqli_fetch_assoc($res)) echo "<option value='{$row['id_insumo']}'>{$row['nombre']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Cantidad</label>
                    <input type="number" id="sel_cantidad" class="form-control" step="0.01">
                </div>
                <div class="col-md-4">
                    <button type="button" id="btn_agregar" class="btn btn-exec w-100">➕ Agregar a la Lista</button>
                </div>
            </div>
            <table class="table table-bordered" id="tabla_detalles">
                <thead class="table-light">
                    <tr><th>Insumo</th><th>Cantidad</th><th>Acción</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <button type="submit" name="procesar" class="btn btn-lg btn-danger w-100 shadow">💾 Generar Vale de Entrega</button>
</form>

<script>
$(document).ready(function(){
    $("#btn_agregar").click(function(){
        let nombre = $("#sel_insumo option:selected").text();
        let id = $("#sel_insumo").val();
        let cant = $("#sel_cantidad").val();
        if(cant == "" || cant <= 0) return alert("Ingrese cantidad válida");

        let fila = `<tr>
            <td>${nombre} <input type="hidden" name="insumo_id[]" value="${id}"></td>
            <td>${cant} <input type="hidden" name="cantidad[]" value="${cant}"></td>
            <td><button type="button" class="btn btn-sm btn-danger btn_eliminar">Eliminar</button></td>
        </tr>`;
        $("#tabla_detalles tbody").append(fila);
        $("#sel_cantidad").val("");
    });

    $(document).on('click', '.btn_eliminar', function(){
        $(this).closest('tr').remove();
    });
});
</script>

<?php include '../includes/footer.php'; ?>


# ----------------------------------------------------------------------------------------
# ARCHIVO: /modulos/reportes.php
# FUNCIÓN: Genera el listado de trámites y ofrece la opción de "Imprimir" cada uno.
# ----------------------------------------------------------------------------------------
<?php 
include '../config/db.php'; 
include '../includes/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h2>📄 Reportes de Movimientos</h2>
</div>

<div class="card card-executivo bg-white">
    <div class="card-body">
        <table class="table table-sm table-hover">
            <thead>
                <tr class="table-light">
                    <th>Fecha/Hora</th>
                    <th>Documento</th>
                    <th>Tipo</th>
                    <th>Entrega</th>
                    <th>Recibe</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = mysqli_query($conexion, "SELECT * FROM movimientos ORDER BY fecha_hora DESC");
                while($row = mysqli_fetch_assoc($res)){
                    $color = ($row['tipo'] == 'Carga') ? 'text-success' : 'text-danger';
                    echo "<tr>
                        <td>{$row['fecha_hora']}</td>
                        <td>{$row['num_documento']}</td>
                        <td class='$color fw-bold'>{$row['tipo']}</td>
                        <td>{$row['responsable_entrega']}</td>
                        <td>{$row['responsable_recibe']}</td>
                        <td><a href='impresion.php?id={$row['id_movimiento']}' target='_blank' class='btn btn-sm btn-outline-dark'>🖨️ Imprimir</a></td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


# ----------------------------------------------------------------------------------------
# ARCHIVO: /modulos/impresion.php
# FUNCIÓN: Genera una vista limpia, sin menús, optimizada para impresión física.
# ----------------------------------------------------------------------------------------
<?php 
include '../config/db.php'; 
$id = $_GET['id'];

$sql_m = mysqli_query($conexion, "SELECT * FROM movimientos WHERE id_movimiento = '$id'");
$m = mysqli_fetch_assoc($sql_m);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Impresión de Trámite - <?php echo $m['num_documento']; ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        body { background: white; color: black; font-size: 12px; }
        .print-container { width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .header-print { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; }
    </style>
</head>
<body onload="window.print()">
    <div class="print-container">
        <div class="header-print">
            <h1>VALE DE <?php echo strtoupper($m['tipo']); ?></h1>
            <h4>Documento: <?php echo $m['num_documento']; ?></h4>
            <p>Fecha de emisión: <?php echo $m['fecha_hora']; ?></p>
        </div>

        <div class="row mb-4">
            <div class="col-6"><strong>Responsable Entrega:</strong> <?php echo $m['responsable_entrega']; ?></div>
            <div class="col-6"><strong>Responsable Recibe:</strong> <?php echo $m['responsable_recibe']; ?></div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th class="text-center">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $det = mysqli_query($conexion, "SELECT d.*, i.nombre FROM detalle_movimientos d JOIN insumos i ON d.id_insumo = i.id_insumo WHERE d.id_movimiento = '$id'");
                while($row = mysqli_fetch_assoc($det)){
                    echo "<tr><td>{$row['nombre']}</td><td class='text-center'>{$row['cantidad']}</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="mt-5 pt-5 row text-center">
            <div class="col-6">
                <div style="border-top: 1px solid #000; width: 200px; margin: auto;">Firma Entrega</div>
            </div>
            <div class="col-6">
                <div style="border-top: 1px solid #000; width: 200px; margin: auto;">Firma Recibe</div>
            </div>
        </div>
    </div>
</body>
</html>

