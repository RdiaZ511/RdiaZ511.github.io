<?php
// db.php
// Configuración y conexión a la base de datos
// Datos de conexión directos (sin archivo .env)

// Variables globales para la conexión PDO
$DB_HOST = 'localhost';
$DB_PORT = '5432';
$DB_NAME = 'appsmith_db';
$DB_USER = 'admin';
$DB_PASS = '0511.diazaponte';

// Construcción del DSN para PostgreSQL
$dsn = "pgsql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};sslmode=disable";

// Devolver los datos de conexión
$connectionData = [
    'dsn' => $dsn,
    'user' => $DB_USER,
    'pass' => $DB_PASS,
    'host' => $DB_HOST,
    'port' => $DB_PORT,
    'database' => $DB_NAME
];

// --- VERIFICACIÓN DE CONEXIÓN ---
try {
    // Intentar crear una conexión PDO
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Si llegamos aquí, la conexión fue exitosa
    //echo "✅ Conexión exitosa a la base de datos PostgreSQL!\n";
    //echo "Usuario: {$DB_USER}\n";
    //echo "Base de datos: {$DB_NAME}\n";
    
    // Opcional: Ejecutar una consulta simple para confirmar que se puede leer
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result && $result['test'] == 1) {
        //echo "✅ Consulta de prueba exitosa (SELECT 1).\n";
    }
    
} catch (PDOException $e) {
    // Si falla, mostramos el error en la consola (útil para depuración)
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "Detalles: " . $e->getCode() . "\n";
    
    // Si quieres que la función de conexión devuelva un error en lugar de los datos, descomenta la siguiente línea:
    // return ['error' => true, 'message' => $e->getMessage()];
}

// Devolver los datos de conexión (ya sea que funcione o falle, para que puedas ver el estado)
// Si quieres que solo devuelva datos si es exitoso, descomenta la siguiente línea y comenta la de arriba:
return $connectionData;
