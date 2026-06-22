<?php
/**
 * test_all.php
 * -------------------------------
 * 1️⃣ Verifica que las extensiones PDO‑PGSQL y pgsql están cargadas.
 * 2️⃣ Intenta la conexión a PostgreSQL con los credenciales indicados.
 * 3️⃣ Ejecuta consultas de diagnóstico (versión, SELECT 1).
 * 4️⃣ Crea una tabla temporal, inserta, lee y elimina un registro.
 * 5️⃣ Imprime resultados legibles en HTML (para abrir con el navegador).
 *
 * Usa este archivo solo en entorno de pruebas. No lo dejes accesible en producción.
 */

/* -------------------------------------------------
 * 0️⃣ Configuración – REEMPLAZA SI ES NECESARIO
 * ------------------------------------------------- */
$host = 'localhost';
$port = 5432;
$db   = 'appsmith_db';
$user = 'admin';
$pass = '0511.diazaponte';

/* -------------------------------------------------
 * 1️⃣ Verificar extensiones requeridas
 * ------------------------------------------------- */
function checkExtension(string $ext): void
{
    if (!extension_loaded($ext)) {
        die(
            "<h2 style='color:red;'>❌ Extensión <code>{$ext}</code> no está cargada.</h2>"
            . "<p>Instala la extensión con <code>sudo apt install php-{$ext}</code> "
            . "y reinicia el servidor web.</p>"
        );
    }
}
checkExtension('pdo_pgsql');
checkExtension('pgsql');

/* -------------------------------------------------
 * 2️⃣ Construir DSN y abrir la conexión PDO
 * ------------------------------------------------- */
$dsn = "pgsql:host={$host};port={$port};dbname={$db}";
try {
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "<h2 style='color:green;'>✅ Conexión PDO establecida.</h2>";
} catch (PDOException $e) {
    die(
        "<h2 style='color:red;'>❌ No se pudo conectar a PostgreSQL</h2>"
        . "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>"
    );
}

/* -------------------------------------------------
 * 3️⃣ Consultas de diagnóstico
 * ------------------------------------------------- */
try {
    // Versión del servidor
    $stmt = $pdo->query('SELECT version() AS ver');
    $row  = $stmt->fetch();
    echo "<p><strong>Versión PostgreSQL:</strong> " .
         htmlspecialchars($row['ver']) . "</p>";

    // Consulta mínima para confirmar que el motor responde
    $stmt = $pdo->query('SELECT 1 AS test');
    $row  = $stmt->fetch();
    echo $row['test'] == 1
        ? "<p>✔️ <code>SELECT 1</code> ejecutado con éxito.</p>"
        : "<p style='color:red;'>❌ <code>SELECT 1</code> falló.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error en consultas de diagnóstico:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

/* -------------------------------------------------
 * 4️⃣ Prueba de escritura/lectura en tabla temporal
 * ------------------------------------------------- */
echo "<hr><h3>🔧 Prueba de escritura/lectura</h3>";

$temporaryTable = 'tmp_test_php_pgsql';

try {
    // 4.1 Crear tabla (si ya existe, la borramos primero)
    $pdo->exec("DROP TABLE IF EXISTS {$temporaryTable}");
    $pdo->exec(
        "CREATE TABLE {$temporaryTable} (
            id   SERIAL PRIMARY KEY,
            dato TEXT NOT NULL
        )"
    );
    echo "<p>✅ Tabla temporal <code>{$temporaryTable}</code> creada.</p>";

    // 4.2 Insertar un registro
    $stmt = $pdo->prepare("INSERT INTO {$temporaryTable} (dato) VALUES (:dato) RETURNING id");
    $stmt->execute(['dato' => 'prueba desde PHP']);
    $insertedId = $stmt->fetchColumn();
    echo "<p>✅ Registro insertado con id = {$insertedId}.</p>";

    // 4.3 Leer el registro
    $stmt = $pdo->prepare("SELECT dato FROM {$temporaryTable} WHERE id = :id");
    $stmt->execute(['id' => $insertedId]);
    $value = $stmt->fetchColumn();
    echo "<p>✅ Lectura exitosa: <code>{$value}</code></p>";

    // 4.4 Borrar el registro y la tabla
    $pdo->exec("DROP TABLE {$temporaryTable}");
    echo "<p>✅ Tabla temporal borrada. Todo limpio.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error en la prueba de escritura/lectura:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

/* -------------------------------------------------
 * 5️⃣ Información de conexión (opcional)
 * ------------------------------------------------- */
echo "<hr><h4>📋 Detalles de conexión usados</h4>";
echo "<ul>
        <li>Host: <code>" . htmlspecialchars($host) . "</code></li>
        <li>Puerto: <code>" . htmlspecialchars($port) . "</code></li>
        <li>Base de datos: <code>" . htmlspecialchars($db) . "</code></li>
        <li>Usuario: <code>" . htmlspecialchars($user) . "</code></li>
      </ul>";

      $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
while ($row = $stmt->fetch()) {
    echo $row['table_name'] . "<br>";
}
?>