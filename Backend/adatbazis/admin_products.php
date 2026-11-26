<?php
// A végpont JSON-választ küld a böngészőnek
header('Content-Type: application/json; charset=utf-8');

// Az admin belépésekor létrehozott munkamenet ellenőrzése
session_start();

function valasz(array $adat, int $statusz = 200): void
{
    http_response_code($statusz);
    echo json_encode($adat, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Csak bejelentkezett admin módosíthatja a termékeket
if (
    empty($_SESSION['admin_id'])
    || ($_SESSION['szerepkor'] ?? '') !== 'admin'
) {
    valasz(['siker' => false, 'hiba' => 'Admin belépés szükséges.'], 401);
}

require_once __DIR__ . '/dbconnect.php';

// GET kérésre visszaküldjük a termékeket és a választható opciókat
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $termekek = $dbConn->query(
            'SELECT
                termek.termek_id,
                valtozat.valtozat_id,
                termek.nev AS termek_nev,
                termek.anyag,
                termek.minta,
                telefonmodell.modell_id,
                telefonmodell.marka,
                telefonmodell.modell_nev,
                valtozat.szin,
                valtozat.ar,
                valtozat.keszlet,
                valtozat.kep_url
             FROM termekek AS termek
             INNER JOIN termek_valtozat AS valtozat
                ON valtozat.termek_id = termek.termek_id
             INNER JOIN telefonmodellek AS telefonmodell
                ON telefonmodell.modell_id = valtozat.modell_id
             ORDER BY termek.termek_id DESC, telefonmodell.marka, telefonmodell.modell_nev'
        )->fetchAll(PDO::FETCH_ASSOC);

        $modellek = $dbConn->query(
            'SELECT modell_id, marka, modell_nev
             FROM telefonmodellek
             WHERE aktiv = 1
             ORDER BY marka, modell_nev'
        )->fetchAll(PDO::FETCH_ASSOC);

        $anyagok = $dbConn->query(
            'SELECT DISTINCT anyag FROM termekek ORDER BY anyag'
        )->fetchAll(PDO::FETCH_COLUMN);

        // Ezek új termékekhez is választható anyagok
        $ujAnyagok = ['Műanyag', 'Polikarbonát', 'Fém', 'Textil'];
        $anyagok = array_values(array_unique(array_merge($anyagok, $ujAnyagok)));
        sort($anyagok, SORT_NATURAL | SORT_FLAG_CASE);

        $mintak = $dbConn->query(
            'SELECT DISTINCT minta FROM termekek ORDER BY minta'
        )->fetchAll(PDO::FETCH_COLUMN);

        $szinek = $dbConn->query(
            'SELECT DISTINCT szin FROM termek_valtozat ORDER BY szin'
        )->fetchAll(PDO::FETCH_COLUMN);

        valasz([
            'siker' => true,
            'termekek' => $termekek,
            'opciok' => [
                'modellek' => $modellek,
                'anyagok' => $anyagok,
                'mintak' => $mintak,
                'szinek' => $szinek,
            ],
        ]);
    } catch (Throwable $hiba) {
        error_log('Admin termékek lekérési hiba: ' . $hiba->getMessage());
        valasz(['siker' => false, 'hiba' => 'A termékek betöltése nem sikerült.'], 500);
    }
}

// A módosítások JSON-adatként, POST kéréssel érkeznek
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    valasz(['siker' => false, 'hiba' => 'Nem megfelelő kérés.'], 405);
}

$bemenet = json_decode(file_get_contents('php://input'), true);

if (!is_array($bemenet)) {
    valasz(['siker' => false, 'hiba' => 'Hibás kérésadat.'], 400);
}

try {
    $muvelet = $bemenet['muvelet'] ?? '';

    // Egy termékváltozat törlése
    if ($muvelet === 'torles') {
        $valtozatId = filter_var(
            $bemenet['valtozat_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$valtozatId) {
            valasz(['siker' => false, 'hiba' => 'Hiányzó változatazonosító.'], 400);
        }

        $dbConn->beginTransaction();

        $termekLekerdezes = $dbConn->prepare(
            'SELECT termek_id FROM termek_valtozat WHERE valtozat_id = :id'
        );
        $termekLekerdezes->execute(['id' => $valtozatId]);
        $termekId = $termekLekerdezes->fetchColumn();

        if (!$termekId) {
            $dbConn->rollBack();
            valasz(['siker' => false, 'hiba' => 'A termékváltozat nem található.'], 404);
        }

        // Rendeléshez tartozó változatot nem törlünk, mert a rendelés hivatkozik rá
        $rendelesEllenorzes = $dbConn->prepare(
            'SELECT COUNT(*) FROM rendeles_tetelek WHERE valtozat_id = :id'
        );
        $rendelesEllenorzes->execute(['id' => $valtozatId]);

        if ((int) $rendelesEllenorzes->fetchColumn() > 0) {
            $dbConn->rollBack();
            valasz([
                'siker' => false,
                'hiba' => 'Ez a változat rendeléshez tartozik, ezért nem törölhető.'
            ], 409);
        }

        $torles = $dbConn->prepare(
            'DELETE FROM termek_valtozat WHERE valtozat_id = :id'
        );
        $torles->execute(['id' => $valtozatId]);

        // Ha nem maradt másik változata, magát a terméket is töröljük
        $maradekValtozatok = $dbConn->prepare(
            'SELECT COUNT(*) FROM termek_valtozat WHERE termek_id = :termek_id'
        );
        $maradekValtozatok->execute(['termek_id' => $termekId]);

        if ((int) $maradekValtozatok->fetchColumn() === 0) {
            $termekTorles = $dbConn->prepare(
                'DELETE FROM termekek WHERE termek_id = :termek_id'
            );
            $termekTorles->execute(['termek_id' => $termekId]);
        }

        $dbConn->commit();
        valasz(['siker' => true, 'uzenet' => 'A változat törlése sikerült.']);
    }

    // Új termék létrehozása vagy meglévő változat módosítása
    if ($muvelet !== 'mentes') {
        valasz(['siker' => false, 'hiba' => 'Ismeretlen művelet.'], 400);
    }

    $termekId = isset($bemenet['termek_id'])
        ? filter_var($bemenet['termek_id'], FILTER_VALIDATE_INT)
        : null;

    $valtozatId = isset($bemenet['valtozat_id'])
        ? filter_var($bemenet['valtozat_id'], FILTER_VALIDATE_INT)
        : null;

    $nev = trim((string) ($bemenet['nev'] ?? ''));
    $anyag = trim((string) ($bemenet['anyag'] ?? ''));
    $minta = trim((string) ($bemenet['minta'] ?? ''));
    $szin = trim((string) ($bemenet['szin'] ?? ''));
    $modellId = filter_var($bemenet['modell_id'] ?? null, FILTER_VALIDATE_INT);
    $ar = $bemenet['ar'] ?? null;
    $keszlet = filter_var($bemenet['keszlet'] ?? null, FILTER_VALIDATE_INT);

    if (
        $nev === ''
        || $anyag === ''
        || $minta === ''
        || $szin === ''
        || !$modellId
        || !is_numeric($ar)
        || (float) $ar <= 0
        || $keszlet === false
        || $keszlet < 0
    ) {
        valasz(['siker' => false, 'hiba' => 'Ellenőrizd a kötelező mezőket.'], 400);
    }

    // Ellenőrizzük, hogy a választott modell tényleg létezik és aktív
    $modellEllenorzes = $dbConn->prepare(
        'SELECT COUNT(*) FROM telefonmodellek
         WHERE modell_id = :id AND aktiv = 1'
    );
    $modellEllenorzes->execute(['id' => $modellId]);

    if ((int) $modellEllenorzes->fetchColumn() === 0) {
        valasz(['siker' => false, 'hiba' => 'A kiválasztott modell nem található.'], 400);
    }

    $dbConn->beginTransaction();

    if ($termekId && $valtozatId) {
        // Módosításkor ellenőrizzük, hogy a változat ehhez a termékhez tartozik-e
        $valtozatEllenorzes = $dbConn->prepare(
            'SELECT COUNT(*) FROM termek_valtozat
             WHERE valtozat_id = :valtozat_id AND termek_id = :termek_id'
        );
        $valtozatEllenorzes->execute([
            'valtozat_id' => $valtozatId,
            'termek_id' => $termekId,
        ]);

        if ((int) $valtozatEllenorzes->fetchColumn() === 0) {
            $dbConn->rollBack();
            valasz(['siker' => false, 'hiba' => 'A szerkesztett változat nem található.'], 404);
        }

        // A terméktábla közös adatait módosítjuk
        $termekFrissites = $dbConn->prepare(
            'UPDATE termekek
             SET nev = :nev, anyag = :anyag, minta = :minta
             WHERE termek_id = :termek_id'
        );
        $termekFrissites->execute([
            'nev' => $nev,
            'anyag' => $anyag,
            'minta' => $minta,
            'termek_id' => $termekId,
        ]);

        // A kiválasztott változat modelljét, színét, árát és készletét módosítjuk
        $valtozatFrissites = $dbConn->prepare(
            'UPDATE termek_valtozat
             SET modell_id = :modell_id, szin = :szin, ar = :ar, keszlet = :keszlet
             WHERE valtozat_id = :valtozat_id'
        );
        $valtozatFrissites->execute([
            'modell_id' => $modellId,
            'szin' => $szin,
            'ar' => $ar,
            'keszlet' => $keszlet,
            'valtozat_id' => $valtozatId,
        ]);
    } else {
        // Új termék esetén először létrehozzuk a termék főadatait
        $ujTermek = $dbConn->prepare(
            'INSERT INTO termekek (nev, leiras, minta, anyag, aktiv)
             VALUES (:nev, :leiras, :minta, :anyag, 1)'
        );
        $ujTermek->execute([
            'nev' => $nev,
            'leiras' => '',
            'minta' => $minta,
            'anyag' => $anyag,
        ]);

        $termekId = (int) $dbConn->lastInsertId();

        // Az új termékhez létrehozzuk a kiválasztott modellváltozatot
        $ujValtozat = $dbConn->prepare(
            'INSERT INTO termek_valtozat
                (termek_id, modell_id, szin, ar, keszlet, kep_url, aktiv)
             VALUES
                (:termek_id, :modell_id, :szin, :ar, :keszlet, :kep_url, 1)'
        );
        $ujValtozat->execute([
            'termek_id' => $termekId,
            'modell_id' => $modellId,
            'szin' => $szin,
            'ar' => $ar,
            'keszlet' => $keszlet,
            'kep_url' => 'placeholder.jpg',
        ]);
    }

    $dbConn->commit();
    valasz(['siker' => true, 'uzenet' => 'A termék mentése sikerült.']);
} catch (Throwable $hiba) {
    if ($dbConn->inTransaction()) {
        $dbConn->rollBack();
    }

    error_log('Admin termékmentési hiba: ' . $hiba->getMessage());
    valasz(['siker' => false, 'hiba' => 'A termék mentése nem sikerült.'], 500);
}
?>