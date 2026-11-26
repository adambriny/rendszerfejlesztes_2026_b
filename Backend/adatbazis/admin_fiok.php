<?php
// A válasz JSON-formátumú
header('Content-Type: application/json; charset=utf-8');

session_start();

// Egységes JSON-választ küldünk
function valasz(array $adat, int $statusz = 200): void
{
    http_response_code($statusz);
    echo json_encode($adat, JSON_UNESCAPED_UNICODE);
    exit;
}

// Csak bejelentkezett admin kezelheti a saját adatait
if (
    empty($_SESSION['admin_id'])
    || ($_SESSION['szerepkor'] ?? '') !== 'admin'
) {
    valasz(['siker' => false, 'hiba' => 'Admin belépés szükséges.'], 401);
}

$metodus = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!in_array($metodus, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    valasz(['siker' => false, 'hiba' => 'Nem megfelelő kérés.'], 405);
}

require_once __DIR__ . '/dbconnect.php';

try {
    $adminId = (int) $_SESSION['admin_id'];

    // GET kérésre visszaadjuk az adatbázisban tárolt adatokat
    if ($metodus === 'GET') {
        $lekerdezes = $dbConn->prepare(
            'SELECT nev, email, szerepkor
             FROM felhasznalok
             WHERE felhasznalo_id = :id AND aktiv = 1'
        );
        $lekerdezes->execute(['id' => $adminId]);
        $fiok = $lekerdezes->fetch(PDO::FETCH_ASSOC);

        if (!$fiok) {
            valasz(['siker' => false, 'hiba' => 'Az adminfiók nem található.'], 404);
        }

        valasz(['siker' => true, 'fiok' => $fiok]);
    }

    // POST kérésnél a módosított név és e-mail érkezik
    $bemenet = json_decode(file_get_contents('php://input'), true);

    if (!is_array($bemenet)) {
        valasz(['siker' => false, 'hiba' => 'Hibás kérésadat.'], 400);
    }

    $nev = trim((string) ($bemenet['nev'] ?? ''));
    $email = trim((string) ($bemenet['email'] ?? ''));

    if ($nev === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        valasz([
            'siker' => false,
            'hiba' => 'Adj meg érvényes nevet és e-mail-címet.'
        ], 400);
    }

    // Ellenőrizzük, hogy másik felhasználó nem használja-e már ezt az e-mail-címet
    $emailEllenorzes = $dbConn->prepare(
        'SELECT COUNT(*)
         FROM felhasznalok
         WHERE email = :email AND felhasznalo_id <> :id'
    );
    $emailEllenorzes->execute([
        'email' => $email,
        'id' => $adminId,
    ]);

    if ((int) $emailEllenorzes->fetchColumn() > 0) {
        valasz(['siker' => false, 'hiba' => 'Ezt az e-mail-címet már használja egy másik fiók.'], 409);
    }

    // Elmentjük az adatokat az aktuális admin fiókjához
    $mentes = $dbConn->prepare(
        'UPDATE felhasznalok
         SET nev = :nev, email = :email
         WHERE felhasznalo_id = :id AND szerepkor = :szerepkor'
    );
    $mentes->execute([
        'nev' => $nev,
        'email' => $email,
        'id' => $adminId,
        'szerepkor' => 'admin',
    ]);

    $_SESSION['admin_nev'] = $nev;

    valasz([
        'siker' => true,
        'uzenet' => 'A fiókadatok mentése sikerült.',
        'fiok' => [
            'nev' => $nev,
            'email' => $email,
            'szerepkor' => 'admin',
        ],
    ]);
} catch (Throwable $hiba) {
    error_log('Admin fiók lekérési vagy mentési hiba: ' . $hiba->getMessage());
    valasz(['siker' => false, 'hiba' => 'A fiókadatok kezelése nem sikerült.'], 500);
}
?>