<?php
// A végpont JSON-adatokat küld a böngészőnek
header('Content-Type: application/json; charset=utf-8');
session_start();

// Egységes JSON-választ küldünk
function valasz(array $adat, int $statusz = 200): void
{
    http_response_code($statusz);
    echo json_encode($adat, JSON_UNESCAPED_UNICODE);
    exit;
}

// Csak bejelentkezett admin férhet hozzá a felhasználókhoz
if (
    empty($_SESSION['admin_id'])
    || ($_SESSION['szerepkor'] ?? '') !== 'admin'
) {
    valasz(['siker' => false, 'hiba' => 'Admin belépés szükséges.'], 401);
}

require_once __DIR__ . '/dbconnect.php';

// GET kérésre lekérjük a felhasználókat
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $felhasznalok = $dbConn->query(
            'SELECT felhasznalo_id, nev, email, szerepkor, aktiv
             FROM felhasznalok
             ORDER BY nev, email'
        )->fetchAll(PDO::FETCH_ASSOC);

        valasz([
            'siker' => true,
            'aktualis_admin_id' => (int) $_SESSION['admin_id'],
            'felhasznalok' => $felhasznalok,
        ]);
    } catch (Throwable $hiba) {
        error_log('Felhasználólista lekérési hiba: ' . $hiba->getMessage());
        valasz(['siker' => false, 'hiba' => 'A felhasználók betöltése nem sikerült.'], 500);
    }
}

// Módosításhoz POST kérést várunk
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    valasz(['siker' => false, 'hiba' => 'Nem megfelelő kérés.'], 405);
}

$bemenet = json_decode(file_get_contents('php://input'), true);

if (!is_array($bemenet)) {
    valasz(['siker' => false, 'hiba' => 'Hibás kérésadat.'], 400);
}

$felhasznaloId = filter_var(
    $bemenet['felhasznalo_id'] ?? null,
    FILTER_VALIDATE_INT
);
$szerepkor = $bemenet['szerepkor'] ?? '';
$aktiv = filter_var($bemenet['aktiv'] ?? null, FILTER_VALIDATE_INT);

if (
    !$felhasznaloId
    || !in_array($szerepkor, ['vasarlo', 'admin'], true)
    || !in_array($aktiv, [0, 1], true)
) {
    valasz(['siker' => false, 'hiba' => 'Ellenőrizd a módosítandó adatokat.'], 400);
}

// A bejelentkezett admin nem módosíthatja a saját hozzáférését
if ($felhasznaloId === (int) $_SESSION['admin_id']) {
    valasz([
        'siker' => false,
        'hiba' => 'A saját adminfiókod szerepkörét vagy állapotát nem módosíthatod.'
    ], 403);
}

try {
    $dbConn->beginTransaction();

    // Betöltjük a módosítandó felhasználó jelenlegi adatait
    $ellenorzes = $dbConn->prepare(
        'SELECT szerepkor, aktiv
         FROM felhasznalok
         WHERE felhasznalo_id = :id
         FOR UPDATE'
    );
    $ellenorzes->execute(['id' => $felhasznaloId]);
    $celFelhasznalo = $ellenorzes->fetch(PDO::FETCH_ASSOC);

    if (!$celFelhasznalo) {
        $dbConn->rollBack();
        valasz(['siker' => false, 'hiba' => 'A felhasználó nem található.'], 404);
    }

    // Nem engedjük az utolsó aktív admint vásárlóvá tenni vagy letiltani
    $utolsoAdminKorlatozasa =
        $celFelhasznalo['szerepkor'] === 'admin'
        && (int) $celFelhasznalo['aktiv'] === 1
        && ($szerepkor !== 'admin' || $aktiv !== 1);

    if ($utolsoAdminKorlatozasa) {
        $adminokSzama = (int) $dbConn->query(
            "SELECT COUNT(*) FROM felhasznalok
             WHERE szerepkor = 'admin' AND aktiv = 1"
        )->fetchColumn();

        if ($adminokSzama <= 1) {
            $dbConn->rollBack();
            valasz([
                'siker' => false,
                'hiba' => 'Az utolsó aktív adminfiók nem módosítható vagy tiltható le.'
            ], 409);
        }
    }

    // Elmentjük a szerepkört és az aktív állapotot
    $mentes = $dbConn->prepare(
        'UPDATE felhasznalok
         SET szerepkor = :szerepkor, aktiv = :aktiv
         WHERE felhasznalo_id = :id'
    );
    $mentes->execute([
        'szerepkor' => $szerepkor,
        'aktiv' => $aktiv,
        'id' => $felhasznaloId,
    ]);

    $dbConn->commit();
    valasz(['siker' => true, 'uzenet' => 'A felhasználó adatai frissültek.']);
} catch (Throwable $hiba) {
    if ($dbConn->inTransaction()) {
        $dbConn->rollBack();
    }

    error_log('Felhasználó-módosítási hiba: ' . $hiba->getMessage());
    valasz(['siker' => false, 'hiba' => 'A felhasználó módosítása nem sikerült.'], 500);
}
?>