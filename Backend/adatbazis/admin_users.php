<?php
// A végpont JSON-adatokat küld a böngészőnek
header('Content-Type: application/json; charset=utf-8');
session_start();

// Egységes JSON-választ küldünk
function valasz(array $adat, int $statusz = 200)
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

// Törlés előtt elmentjük a felhasználó teljes rendelési adatait JSON-fájlba (ami még nincs neki)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $bemenet = json_decode(file_get_contents('php://input'), true);
    $felhasznaloId = filter_var(
        $bemenet['felhasznalo_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$felhasznaloId) {
        valasz(['siker' => false, 'hiba' => 'Érvénytelen felhasználóazonosító.'], 400);
    }

    if ($felhasznaloId === (int) $_SESSION['admin_id']) {
        valasz(['siker' => false, 'hiba' => 'A saját adminfiókodat nem törölheted.'], 403);
    }

    $archivFajl = null;

    try {
        $dbConn->beginTransaction();

        $felhasznaloLe = $dbConn->prepare(
            'SELECT felhasznalo_id, nev, email, szerepkor, aktiv
             FROM felhasznalok
             WHERE felhasznalo_id = :id
             FOR UPDATE'
        );
        $felhasznaloLe->execute(['id' => $felhasznaloId]);
        $felhasznalo = $felhasznaloLe->fetch(PDO::FETCH_ASSOC);

        if (!$felhasznalo) {
            $dbConn->rollBack();
            valasz(['siker' => false, 'hiba' => 'A felhasználó nem található.'], 404);
        }

        if ($felhasznalo['szerepkor'] === 'admin' && (int) $felhasznalo['aktiv'] === 1) {
            $aktivAdminok = (int) $dbConn->query(
                "SELECT COUNT(*) FROM felhasznalok
                 WHERE szerepkor = 'admin' AND aktiv = 1"
            )->fetchColumn();

            if ($aktivAdminok <= 1) {
                $dbConn->rollBack();
                valasz([
                    'siker' => false,
                    'hiba' => 'Az utolsó aktív adminfiók nem törölhető.'
                ], 409);
            }
        }

        $rendelesLe = $dbConn->prepare(
            'SELECT rendeles_id, rendeles_datuma, statusz,
                    szallitasi_adatok, szamlazasi_adatok
             FROM rendelesek
             WHERE felhasznalo_id = :id
             ORDER BY rendeles_datuma, rendeles_id'
        );
        $rendelesLe->execute(['id' => $felhasznaloId]);
        $rendelesek = $rendelesLe->fetchAll(PDO::FETCH_ASSOC);

        $tetelLe = $dbConn->prepare(
            'SELECT tetel.tetel_id, tetel.valtozat_id,
                    tetel.mennyiseg, tetel.egysegar,
                    termek.nev AS termek_nev,
                    modell.marka, modell.modell_nev, valtozat.szin
             FROM rendeles_tetelek AS tetel
             LEFT JOIN termek_valtozat AS valtozat
                 ON valtozat.valtozat_id = tetel.valtozat_id
             LEFT JOIN termekek AS termek
                 ON termek.termek_id = valtozat.termek_id
             LEFT JOIN telefonmodellek AS modell
                 ON modell.modell_id = valtozat.modell_id
             WHERE tetel.rendeles_id = :rendeles_id
             ORDER BY tetel.tetel_id'
        );

        foreach ($rendelesek as &$rendeles) {
            $tetelLe->execute(['rendeles_id' => $rendeles['rendeles_id']]);
            $rendeles['tetelek'] = $tetelLe->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($rendeles);

        $archivAdat = [
            'archivalas_ideje' => date(DATE_ATOM),
            'felhasznalo' => [
                'felhasznalo_id' => (int) $felhasznalo['felhasznalo_id'],
                'nev' => $felhasznalo['nev'],
                'email' => $felhasznalo['email'],
                'szerepkor' => $felhasznalo['szerepkor'],
            ],
            'rendelesek' => $rendelesek,
        ];

        $archivKonyvtar = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'archiv_rendelesek';

        if (!is_dir($archivKonyvtar)
            && !mkdir($archivKonyvtar, 0700, true)
            && !is_dir($archivKonyvtar)
        ) {
            throw new RuntimeException('Az archívumkönyvtár nem hozható létre.');
        }

        $tiltasFajl = $archivKonyvtar . DIRECTORY_SEPARATOR . '.htaccess';

        if (!is_file($tiltasFajl)
            && file_put_contents($tiltasFajl, "Require all denied\n", LOCK_EX) === false)# Az archív fájlok közvetlen webes elérése tiltott
        {
            throw new RuntimeException('Az archívum webes elérését nem sikerült letiltani.');
        }

        $archivNev = 'felhasznalo_' . $felhasznaloId . '_'
            . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.json';
        $archivFajl = $archivKonyvtar . DIRECTORY_SEPARATOR . $archivNev;

        $json = json_encode(
            $archivAdat,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false || file_put_contents($archivFajl, $json, LOCK_EX) === false) {
            throw new RuntimeException('A rendelési archívum mentése nem sikerült.');
        }

        $rendelesAzonositok = array_column($rendelesek, 'rendeles_id');

        if ($rendelesAzonositok !== []) {
            $helyek = implode(',', array_fill(0, count($rendelesAzonositok), '?'));

            $tetelTorles = $dbConn->prepare(
                "DELETE FROM rendeles_tetelek WHERE rendeles_id IN ($helyek)"
            );
            $tetelTorles->execute($rendelesAzonositok);

            $rendelesTorles = $dbConn->prepare(
                "DELETE FROM rendelesek WHERE rendeles_id IN ($helyek)"
            );
            $rendelesTorles->execute($rendelesAzonositok);
        }

        $felhasznaloTorles = $dbConn->prepare(
            'DELETE FROM felhasznalok WHERE felhasznalo_id = :id'
        );
        $felhasznaloTorles->execute(['id' => $felhasznaloId]);

        $dbConn->commit();

        valasz([
            'siker' => true,
            'uzenet' => 'A fiók törölve, a rendelések archiválva.',
            'archivalt_rendelesek' => count($rendelesek),
        ]);
    } catch (Throwable $hiba) {
        if ($dbConn->inTransaction()) {
            $dbConn->rollBack();
        }

        if ($archivFajl !== null && is_file($archivFajl)) {
            unlink($archivFajl);
        }

        error_log('Felhasználó törlési hiba: ' . $hiba->getMessage());

        valasz([
            'siker' => false,
            'hiba' => 'A törlés vagy a rendelési archívum mentése nem sikerült.'
        ], 500);
    }
}

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

// Módosításhoz POST, törléshez DELETE kérés érkezik
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'], true)) {
    header('Allow: GET, POST, DELETE');
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