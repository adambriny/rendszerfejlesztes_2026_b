<?php
// A böngészőnek jelezzük, hogy a válasz JSON formátumú lesz 
header('Content-Type: application/json; charset=utf-8');

// Egységesen küldünk JSON-választ beállítjuk a HTTP-t majd lezárjuk a kérést
function valasz(array $adat, int $statusz = 200): void
{
    http_response_code($statusz);
    echo json_encode($adat, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// A végpont csak GET és POST kéréseket fogad el
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    valasz([
        'siker' => false,
        'hiba' => 'Csak GET és POST kérés engedélyezett.'
    ], 405);
}

// Betöltjük a PDO-adatbázis-kapcsolatot
require_once __DIR__ . '/dbconnect.php';

// Összegyűjtjük a POST- és GET-adatokat
// Ha ugyanaz az adat mindkettőben szerepel, a GET-ben érkező érték lesz érvényes
$bemenet = array_merge($_POST, $_GET);

// Egyetlen szöveges bemeneti értéket olvas ki
// Tömböt nem fogad el, így elkerüljük a hibás típusú bemenetet
$mezo = static function (string $nev) use ($bemenet): string {
    $ertek = $bemenet[$nev] ?? '';

    return is_string($ertek) || is_numeric($ertek)
        ? trim((string) $ertek)
        : '';
};

// Többértékű szűrőhöz olvassa ki és tisztítja meg az értékeket
// például az anyag[] és minta[] pipás adatait
$listaMezo = static function (string $nev) use ($bemenet): array {
    $ertekek = $bemenet[$nev] ?? [];

    // Egyetlen értéket is tömbként kezelünk hogy a feldolgozás ugyanolyan legyen
    if (!is_array($ertekek)) {
        $ertekek = [$ertekek];
    }

    $lista = [];

    foreach ($ertekek as $ertek) {
        if (is_string($ertek) || is_numeric($ertek)) {
            $ertek = trim((string) $ertek);

            if ($ertek !== '') {
                $lista[] = $ertek;
            }
        }
    }

    // Eltávolítjuk az ismétlődő értékeket
    return array_values(array_unique($lista));
};

// Kiolvassuk a kiválasztott anyagokat és mintákat
$anyagok = $listaMezo('anyag');
$mintak = $listaMezo('minta');

// Kiolvassuk az egyértékű márka, modell és színszűrőket
$szurok = [
    'marka' => $mezo('marka'),
    'modell' => $mezo('modell'),
    'szin' => $mezo('szin'),
];

// Kiolvassuk a keresősávba beírt kifejezést
$keresettSzo = $mezo('kereso');

// Nem engedünk 100 karakternél hosszabb keresési vagy szűrési értékeket
foreach (array_merge(array_values($szurok), [$keresettSzo], $anyagok, $mintak) as $ertek) {
    $hossz = function_exists('mb_strlen')
        ? mb_strlen($ertek, 'UTF-8')
        : strlen($ertek);

    if ($hossz > 100) {
        valasz([
            'siker' => false,
            'hiba' => 'A keresési érték legfeljebb 100 karakter lehet.'
        ], 400);
    }
}

// A keresőkifejezést szavakra bontjuk 
// Minden szó külön keresési feltételt kap 
$keresesiSzavak = preg_split('/\s+/u', trim($keresettSzo)) ?: [];
$keresesiSzavak = array_values(array_filter($keresesiSzavak));

// A kiválasztott szűrők és keresőszavak alapján lekéri a termékeket 
$lekerdezTermekeket = static function (
    array $szurok,
    array $anyagok,
    array $mintak,
    array $szavak
) use ($dbConn): array {
    $feltetelek = [];
    $parameterek = [];

    // Ezek az egyértékű szűrők adatbázis-oszlopai 
    $szuroOszlopok = [
        'marka' => 'telefonmodell.marka',
        'modell' => 'telefonmodell.modell_nev',
        'szin' => 'valtozat.szin',
    ];

    // Csak a megadott márkát, modellt vagy színt vesszük figyelembe
    foreach ($szuroOszlopok as $kulcs => $oszlop) {
        if ($szurok[$kulcs] !== '') {
            $feltetelek[] = $oszlop . ' = :szuro_' . $kulcs;
            $parameterek['szuro_' . $kulcs] = $szurok[$kulcs];
        }
    }

    // Az anyag és mintaszűrő több értéket fogadhat
    // Egy kategórián belül bármelyik kiválasztott érték megfelelhet
    foreach ([
        'anyag' => ['termek.anyag', $anyagok],
        'minta' => ['termek.minta', $mintak],
    ] as $nev => [$oszlop, $ertekek]) {
        if ($ertekek !== []) {
            $helyek = [];

            foreach ($ertekek as $index => $ertek) {
                $paramNev = $nev . '_' . $index;
                $helyek[] = ':' . $paramNev;
                $parameterek[$paramNev] = $ertek;
            }

            $feltetelek[] = $oszlop . ' IN (' . implode(', ', $helyek) . ')';
        }
    }

    // Minden keresőszónak szerepelnie kell a terméknévben, mintában,  modellnévben vagy márkanévben
    foreach ($szavak as $index => $szo) {
        $nevParam = ':nev_' . $index;
        $mintaParam = ':minta_szo_' . $index;
        $modellParam = ':modell_szo_' . $index;
        $markaParam = ':marka_szo_' . $index;

        $feltetelek[] = '(termek.nev LIKE ' . $nevParam
            . ' OR termek.minta LIKE ' . $mintaParam
            . ' OR telefonmodell.modell_nev LIKE ' . $modellParam
            . ' OR telefonmodell.marka LIKE ' . $markaParam . ')';

        // A százalékjelek részleges szövegegyezést segítik
        $keresesiMinta = '%' . $szo . '%';
        $parameterek['nev_' . $index] = $keresesiMinta;
        $parameterek['minta_szo_' . $index] = $keresesiMinta;
        $parameterek['modell_szo_' . $index] = $keresesiMinta;
        $parameterek['marka_szo_' . $index] = $keresesiMinta;
    }

    // A termékeket összekapcsoljuk a változataikkal és a telefonmodellekkel
    $sql = <<<'SQL'
SELECT
    termek.termek_id,
    termek.nev AS termek_nev,
    termek.leiras,
    termek.minta,
    termek.anyag,
    telefonmodell.modell_id,
    telefonmodell.marka,
    telefonmodell.modell_nev AS telefon_modell,
    valtozat.szin,
    valtozat.ar,
    valtozat.keszlet,
    valtozat.kep_url
FROM termekek AS termek
INNER JOIN termek_valtozat AS valtozat
    ON valtozat.termek_id = termek.termek_id
INNER JOIN telefonmodellek AS telefonmodell
    ON telefonmodell.modell_id = valtozat.modell_id
SQL;

    // A WHERE részt csak akkor adjuk hozzá ha van keresési feltétel
    if ($feltetelek !== []) {
        $sql .= "\nWHERE " . implode("\n  AND ", $feltetelek);
    }

    // A találatok sorrendje legyen kiszámítható
    $sql .= "\nORDER BY termek.nev, telefonmodell.marka, telefonmodell.modell_nev, valtozat.szin";

    // Előkészítjük és paraméterezve futtatjuk le az SQL-lekérdezést
    $lekerdezes = $dbConn->prepare($sql);
    $lekerdezes->execute($parameterek);

    return $lekerdezes->fetchAll(PDO::FETCH_ASSOC);
};

try {
    // Lekérjük a szűrőknek és a keresőkifejezésnek megfelelő termékeket 
    $termekek = $lekerdezTermekeket($szurok, $anyagok, $mintak, $keresesiSzavak);
    $uzenet = '';

    // Ha nincs találat megpróbálunk hasonló modell, termék vagy minta nevet ajánlani
    if ($termekek === [] && $keresettSzo !== '') {
        $szurozottTermekek = $lekerdezTermekeket($szurok, $anyagok, $mintak, []);
        $legjobbTalalat = '';
        $maxSzazalek = 0.0;

        foreach ($szurozottTermekek as $sor) {
            foreach ([$sor['telefon_modell'], $sor['termek_nev'], $sor['minta']] as $jelolt) {
                $keresettKisbetus = function_exists('mb_strtolower')
                    ? mb_strtolower($keresettSzo, 'UTF-8')
                    : strtolower($keresettSzo);

                $jeloltKisbetus = function_exists('mb_strtolower')
                    ? mb_strtolower((string) $jelolt, 'UTF-8')
                    : strtolower((string) $jelolt);

                similar_text($keresettKisbetus, $jeloltKisbetus, $szazalek);

                if ($szazalek > $maxSzazalek) {
                    $maxSzazalek = $szazalek;
                    $legjobbTalalat = (string) $jelolt;
                }
            }
        }

        // Ha az egyezés elég közeli az ajánlott kifejezéssel is lekérjük a termékeket
        if ($maxSzazalek > 50 && $legjobbTalalat !== '') {
            $termekek = $lekerdezTermekeket(
                $szurok,
                $anyagok,
                $mintak,
                [$legjobbTalalat]
            );

            $uzenet = 'Erre gondoltál: ' . $legjobbTalalat . '?';
        }
    }

    // Az opciókat az adatbázisból olvassuk ki így csak ténylegesen létező értékek látunk
    $opciok = [
        'markak' => $dbConn->query(
            'SELECT DISTINCT telefonmodell.marka
             FROM telefonmodellek AS telefonmodell
             INNER JOIN termek_valtozat AS valtozat
                 ON valtozat.modell_id = telefonmodell.modell_id
             ORDER BY telefonmodell.marka'
        )->fetchAll(PDO::FETCH_COLUMN),

        'modellek' => $dbConn->query(
            'SELECT DISTINCT telefonmodell.modell_nev
             FROM telefonmodellek AS telefonmodell
             INNER JOIN termek_valtozat AS valtozat
                 ON valtozat.modell_id = telefonmodell.modell_id
             ORDER BY telefonmodell.modell_nev'
        )->fetchAll(PDO::FETCH_COLUMN),

        'anyagok' => $dbConn->query(
            'SELECT DISTINCT termek.anyag
             FROM termekek AS termek
             INNER JOIN termek_valtozat AS valtozat
                 ON valtozat.termek_id = termek.termek_id
             ORDER BY termek.anyag'
        )->fetchAll(PDO::FETCH_COLUMN),

        'mintak' => $dbConn->query(
            'SELECT DISTINCT termek.minta
             FROM termekek AS termek
             INNER JOIN termek_valtozat AS valtozat
                 ON valtozat.termek_id = termek.termek_id
             ORDER BY termek.minta'
        )->fetchAll(PDO::FETCH_COLUMN),

        'szinek' => $dbConn->query(
            'SELECT DISTINCT valtozat.szin
             FROM termek_valtozat AS valtozat
             INNER JOIN termekek AS termek
                 ON termek.termek_id = valtozat.termek_id
             ORDER BY valtozat.szin'
        )->fetchAll(PDO::FETCH_COLUMN),
    ];

    // A termékek mellett az aktuális szűrőopciókat és a javaslatot is visszaküldjük
    valasz([
        'siker' => true,
        'talalatok' => $termekek,
        'uzenet' => $uzenet,
        'opciok' => $opciok,
    ]);
} catch (Throwable $hiba) {
    // A részletes hibát a szervernaplóba írjuk a kliens csak általános üzenetet kap
    error_log('Terméklekérdezési hiba: ' . $hiba->getMessage());

    valasz([
        'siker' => false,
        'hiba' => 'A termékek lekérése nem sikerült.'
    ], 500);
}
?>