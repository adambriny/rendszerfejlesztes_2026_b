<?php
$env = parse_ini_file(__DIR__ . '/../../.env');
try {
    $adatforras = $env['DB_TYPE'] . ':host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'] . ';charset=' . $env['DB_CHARSET'];
    $dbConn = new PDO($adatforras, $env['DB_USER'], $env['DB_PASS']);
    $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $p) {
    die("Hiba történt az adatbázishoz való csatlakozás során. Kérjük, próbálja meg később.");
}
?>