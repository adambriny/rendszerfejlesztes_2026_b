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
    // Csak aktív fiókot keresünk, így az inaktív fiók is általános hibát kap
    $lekerdezes = $dbConn->prepare(
        'SELECT felhasznalo_id, nev, jelszo_hash, szerepkor
        FROM felhasznalok
        WHERE email = :email AND aktiv = 1'
    );
    $lekerdezes->execute(['email' => $email]);
    $felhasznalo = $lekerdezes->fetch(PDO::FETCH_ASSOC);

    $jelszoJo = false;

    if ($felhasznalo) {
        $jelszoJo = password_verify($jelszo, $felhasznalo['jelszo_hash']);

        // A régi, sima szövegű mintajelszót sikeres belépéskor hash-re cseréljük
        if (!$jelszoJo && hash_equals($felhasznalo['jelszo_hash'], $jelszo)) {
            $ujHash = password_hash($jelszo, PASSWORD_DEFAULT);
            $jelszoMentes = $dbConn->prepare(
                'UPDATE felhasznalok
                SET jelszo_hash = :jelszo_hash
                WHERE felhasznalo_id = :id'
            );
            $jelszoMentes->execute([
                'jelszo_hash' => $ujHash,
                'id' => $felhasznalo['felhasznalo_id'],
            ]);
            $jelszoJo = true;
        }
    }

    if (!$felhasznalo || !$jelszoJo) {
        valasz(['siker' => false, 'hiba' => 'Hibás e-mail-cím vagy jelszó.'], 401);
    }

    session_regenerate_id(true);
    $_SESSION = [];
    $_SESSION['felhasznalo_id'] = (int) $felhasznalo['felhasznalo_id'];
    $_SESSION['felhasznalo_nev'] = $felhasznalo['nev'];
    $_SESSION['szerepkor'] = $felhasznalo['szerepkor'];

    // Az admin oldalak a meglévő admin munkamenet-változókat használják
    if ($felhasznalo['szerepkor'] === 'admin') {
        $_SESSION['admin_id'] = (int) $felhasznalo['felhasznalo_id'];
        $_SESSION['admin_nev'] = $felhasznalo['nev'];
    }

    valasz([
        'siker' => true,
        'szerepkor' => $felhasznalo['szerepkor'],
    ]);
} catch (Throwable $hiba) {
    error_log('Admin belépési hiba: ' . $hiba->getMessage());
    valasz(['siker' => false, 'hiba' => 'A belépés jelenleg nem sikerült.'], 500);
}
?>