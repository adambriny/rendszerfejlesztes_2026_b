<?php
// A válasz JSON-formátumú lesz
header('Content-Type: application/json; charset=utf-8');

// A munkamenet-cookie nem érhető el JavaScriptből
$httpsKapcsolat = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $httpsKapcsolat,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

// Egységes JSON-választ küldünk majd lezárjuk a kérést
function valasz(array $adat, int $statusz = 200): void
{
    http_response_code($statusz);
    echo json_encode($adat, JSON_UNESCAPED_UNICODE);
    exit;
}

// A belépéshez csak POST kérés használható
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    valasz(['siker' => false, 'hiba' => 'Nem megfelelő kérés.'], 405);
}

// JSON-ból olvassuk a böngésző által küldött adatokats
$bemenet = json_decode(file_get_contents('php://input'), true);

if (!is_array($bemenet)) {
    $bemenet = $_POST;
}

$email = trim((string) ($bemenet['email'] ?? ''));
$jelszo = (string) ($bemenet['jelszo'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $jelszo === '') {
    valasz(['siker' => false, 'hiba' => 'Hibás e-mail-cím vagy jelszó.'], 400);
}

require_once __DIR__ . '/dbconnect.php';

try {
    // Csak aktív adminfiókot keresünk
    $lekerdezes = $dbConn->prepare(
        'SELECT felhasznalo_id, nev, jelszo_hash
         FROM felhasznalok
         WHERE email = :email AND szerepkor = :szerepkor AND aktiv = 1'
    );

    $lekerdezes->execute([
        'email' => $email,
        'szerepkor' => 'admin',
    ]);

    $admin = $lekerdezes->fetch(PDO::FETCH_ASSOC);

    // A megadott jelszót összehasonlítjuk az adatbázisban tárolt hash-sel
    if (!$admin || !password_verify($jelszo, $admin['jelszo_hash'])) {
        valasz(['siker' => false, 'hiba' => 'Hibás e-mail-cím vagy jelszó.'], 401);
    }

    // Új munkamenet-azonosítót adunk a sikeres belépés után
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['felhasznalo_id'];
    $_SESSION['admin_nev'] = $admin['nev'];
    $_SESSION['szerepkor'] = 'admin';

    valasz(['siker' => true]);
} catch (Throwable $hiba) {
    error_log('Admin belépési hiba: ' . $hiba->getMessage());
    valasz(['siker' => false, 'hiba' => 'A belépés jelenleg nem sikerült.'], 500);
}
?>