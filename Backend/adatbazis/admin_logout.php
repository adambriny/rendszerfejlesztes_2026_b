<?php
// A kijelentkezési válasz JSON-formátumú
header('Content-Type: application/json; charset=utf-8');

session_start();

// Töröljük az admin munkamenet adatait
$_SESSION = [];
session_destroy();

echo json_encode(['siker' => true], JSON_UNESCAPED_UNICODE);
?>