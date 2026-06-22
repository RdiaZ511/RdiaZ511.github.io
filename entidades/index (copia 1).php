<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/* -------------------------------------------------
   index.php  –  Re‑crea la UI de Appsmith en PHP
   ------------------------------------------------- */

/* ---------- CONFIGURACIÓN DE LA BASE DE DATOS ----------
   Con credenciales que ya me entregaste
------------------------------------------------------------------ */
$dsn  = "pgsql:host=localhost;port=5432;dbname=appsmith_db";
$user = "admin";
$pass = "0511.diazaponte";

try {
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("<h1>Error de conexión</h1><p>{$e->getMessage()}</p>");
}

/* ------------------- CONSULTAS ------------------- */
function fetchAllRows(PDO $pdo)
{
    $sql = <<<SQL
        SELECT
            codigo,
            entidad,
            identificacion,
            municipio_parroquia,
            direccion,
            ubicacion,
            fecha_inicio,
            telefono,
            tipo_entidad,
            tamano_entidad,
            contacto,
            telefono_contacto,
            web_entidad,
            instagram_entidad,
            twitter_entidad,
            facebook_entidad,
            capital_humano,
            productos,
            capacidad_produccion,
            instalada,
            operativa,
            participacion_mercado,
            materia_prima
        FROM entidad
        ORDER BY identificacion;
    SQL;

    return $pdo->query($sql)->fetchAll();
}

function insertRow(PDO $pdo, array $data)
{
    $sql = <<<SQL
        INSERT INTO entidad
        (codigo, entidad, identificacion, municipio_parroquia, direccion, ubicacion,
         fecha_inicio, telefono, tipo_entidad, tamano_entidad, contacto,
         telefono_contacto, web_entidad, instagram_entidad, twitter_entidad,
         facebook_entidad, capital_humano, productos, capacidad_produccion,
         instalada, operativa, participacion_mercado, materia_prima)
        VALUES
        (:codigo, :entidad, :identificacion, :municipio_parroquia, :direccion, :ubicacion,
         :fecha_inicio, :telefono, :tipo_entidad, :tamano_entidad, :contacto,
         :telefono_contacto, :web_entidad, :instagram_entidad, :twitter_entidad,
         :facebook_entidad, :capital_humano, :productos, :capacidad_produccion,
         :instalada, :operativa, :participacion_mercado, :materia_prima);
    SQL;

    $pdo->prepare($sql)->execute($data);
}

function updateRow(PDO $pdo, $id, array $data)
{
    $sql = <<<SQL
        UPDATE entidad SET
            entidad = :entidad,
            identificacion = :identificacion,
            municipio_parroquia = :municipio_parroquia,
            direccion = :direccion,
            ubicacion = :ubicacion,
            fecha_inicio = :fecha_inicio,
            telefono = :telefono,
            tipo_entidad = :tipo_entidad,
            tamano_entidad = :tamano_entidad,
            contacto = :contacto,
            telefono_contacto = :telefono_contacto,
            web_entidad = :web_entidad,
            instagram_entidad = :instagram_entidad,
            twitter_entidad = :twitter_entidad,
            facebook_entidad = :facebook_entidad,
            capital_humano = :capital_humano,
            productos = :productos,
            capacidad_produccion = :capacidad_produccion,
            instalada = :instalada,
            operativa = :operativa,
            participacion_mercado = :participacion_mercado,
            materia_prima = :materia_prima
        WHERE codigo = :codigo;
    SQL;

    $data['codigo'] = $id;
    $pdo->prepare($sql)->execute($data);
}

function deleteRow(PDO $pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM entidad WHERE codigo = :codigo");
    $stmt->execute(['codigo' => $id]);
}

/* ------------------- HANDLING DE POST ------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insert
    if (isset($_POST['action']) && $_POST['action'] === 'insert') {
        insertRow($pdo, $_POST);
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }

    // Update
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = (int)$_POST['codigo_original'];
        updateRow($pdo, $id, $_POST);
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }

    // Delete
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        deleteRow($pdo, (int)$_POST['codigo']);
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }
}

/* ------------------- FETCH DE DATOS ------------------- */
$rows = fetchAllRows($pdo);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    Entidades Económicas – Estado Carabobo
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <style>
        .modal-header .btn-close {margin: -1rem -1rem -1rem auto;}
    </style>
</head>
<body class="bg-light">

<div class="container py-4">

    <h1 class="mb-4">Entidades Económicas – Estado Carabobo</h1>

    <!-- ---------- Botones de Acción ---------- -->
    <div class="d-flex justify-content-between mb-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#insertModal">
            <i class="bi bi-plus-lg"></i> Agregar
        </button>
        <button class="btn btn-outline-secondary" id="refreshBtn">
            <i class="bi bi-arrow-clockwise"></i> Refrescar
        </button>
    </div>

    <!-- ---------- Tabla ---------- -->
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Entidad</th>
                <th>Identificación</th>
                <th>Municipio/Parroquia</th>
                <th>Dirección</th>
                <th>Ubicación</th>
                <th>Inicio</th>
                <th>Teléfono</th>
                <th>Tipo</th>
                <th>Tamaño</th>
                <th>Contacto</th>
                <th>Tel. Contacto</th>
                <th>Web</th>
                <th>Instagram</th>
                <th>Twitter</th>
                <th>Facebook</th>
                <th>Capital Humano</th>
                <th>Productos</th>
                <th>Cap. Producción</th>
                <th>Instalada</th>
                <th>Operativa</th>
                <th>Mercado</th>
                <th>Materia Prima</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['codigo']) ?></td>
                <td><?= htmlspecialchars($r['entidad']) ?></td>
                <td><?= htmlspecialchars($r['identificacion']) ?></td>
                <td><?= htmlspecialchars($r['municipio_parroquia']) ?></td>
                <td><?= htmlspecialchars($r['direccion']) ?></td>
                <td><?= htmlspecialchars($r['ubicacion']) ?></td>
                <td><?= htmlspecialchars($r['fecha_inicio']) ?></td>
                <td><?= htmlspecialchars($r['telefono']) ?></td>
                <td><?= htmlspecialchars($r['tipo_entidad']) ?></td>
                <td><?= htmlspecialchars($r['tamano_entidad']) ?></td>
                <td><?= htmlspecialchars($r['contacto']) ?></td>
                <td><?= htmlspecialchars($r['telefono_contacto']) ?></td>
                <td><?= htmlspecialchars($r['web_entidad']) ?></td>
                <td><?= htmlspecialchars($r['instagram_entidad']) ?></td>
                <td><?= htmlspecialchars($r['twitter_entidad']) ?></td>
                <td><?= htmlspecialchars($r['facebook_entidad']) ?></td>
                <td><?= htmlspecialchars($r['capital_humano']) ?></td>
                <td><?= htmlspecialchars($r['productos']) ?></td>
                <td><?= htmlspecialchars($r['capacidad_produccion']) ?></td>
                <td><?= htmlspecialchars($r['instalada']) ?></td>
                <td><?= htmlspecialchars($r['operativa']) ?></td>
                <td><?= htmlspecialchars($r['participacion_mercado']) ?></td>
                <td><?= htmlspecialchars($r['materia_prima']) ?></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary me-1"
                            data-bs-toggle="modal"
                            data-bs-target="#updateModal"
                            data-row='<?= json_encode($r) ?>'>
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="post" class="d-inline"
                          onsubmit="return confirm('¿Eliminar este registro?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="codigo" value="<?= $r['codigo'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ==================== MODAL INSERTAR ==================== -->
<div class="modal fade" id="insertModal" tabindex="-1" aria-labelledby="insertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="insertModalLabel">Agregar Entidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <?php
                $fields = [
                    'codigo'               => ['type'=>'number','label'=>'Código','required'=>true],
                    'entidad'              => ['type'=>'text','label'=>'Entidad','required'=>true],
                    'identificacion'       => ['type'=>'text','label'=>'Identificación','required'=>true],
                    'municipio_parroquia'  => ['type'=>'number','label'=>'Municipio/Parroquia'],
                    'direccion'            => ['type'=>'text','label'=>'Dirección'],
                    'ubicacion'            => ['type'=>'text','label'=>'Ubicación'],
                    'fecha_inicio'         => ['type'=>'date','label'=>'Fecha de inicio'],
                    'telefono'             => ['type'=>'text','label'=>'Teléfono'],
                    'tipo_entidad'         => ['type'=>'number','label'=>'Tipo de entidad'],
                    'tamano_entidad'       => ['type'=>'number','label'=>'Tamaño de entidad'],
                    'contacto'             => ['type'=>'text','label'=>'Contacto'],
                    'telefono_contacto'    => ['type'=>'text','label'=>'Teléfono contacto'],
                    'web_entidad'          => ['type'=>'text','label'=>'Web'],
                    'instagram_entidad'    => ['type'=>'text','label'=>'Instagram'],
                    'twitter_entidad'      => ['type'=>'text','label'=>'Twitter'],
                    'facebook_entidad'     => ['type'=>'text','label'=>'Facebook'],
                    'capital_humano'       => ['type'=>'number','label'=>'Capital humano'],
                    'productos'            => ['type'=>'text','label'=>'Productos'],
                    'capacidad_produccion'=> ['type'=>'text','label'=>'Capacidad producción'],
                    'instalada'            => ['type'=>'number','label'=>'Instalada'],
                    'operativa'            => ['type'=>'number','label'=>'Operativa'],
                    'participacion_mercado'=> ['type'=>'number','label'=>'Participación mercado'],
                    'materia_prima'        => ['type'=>'text','label'=>'Materia prima'],
                ];
                foreach ($fields as $name=>$cfg):
                ?>
                    <div class="mb-3">
                        <label class="form-label"><?= $cfg['label'] ?></label>
                        <input type="<?= $cfg['type'] ?>"
                               name="<?= $name ?>"
                               class="form-control"
                               <?= $cfg['required'] ? 'required' : '' ?>>
                    </div>
                <?php endforeach; ?>
                <input type="hidden" name="action" value="insert">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL ACTUALIZAR ==================== -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Editar Entidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="updateFormBody">
                <!-- Los campos se rellenan vía JavaScript -->
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="codigo_original" id="codigo_original">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.js"></script>

<script>
/* ---------- Llenado dinámico del modal de actualización ---------- */
document.getElementById('updateModal').addEventListener('show.bs.modal', function (event) {
    const button   = event.relatedTarget;
    const rowData  = JSON.parse(button.getAttribute('data-row'));
    const body     = document.getElementById('updateFormBody');
    body.innerHTML = '';

    const fields = <?= json_encode($fields) ?>;
    Object.entries(fields).forEach(([name, cfg]) => {
        const div = document.createElement('div');
        div.className = 'mb-3';
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = cfg.label;
        const input = document.createElement('input');
        input.type          = cfg.type;
        input.name          = name;
        input.className     = 'form-control';
        input.value         = rowData[name] ?? '';
        if (cfg.required) input.required = true;
        div.appendChild(label);
        div.appendChild(input);
        body.appendChild(div);
    });

    document.getElementById('codigo_original').value = rowData['codigo'];
});

/* ---------- Refresh button ---------- */
document.getElementById('refreshBtn').addEventListener('click', () => location.reload());
</script>
</body>
</html>