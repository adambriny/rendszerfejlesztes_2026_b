<?php
// A böngészőnek jelezzük, hogy a válasz JSON formátumú lesz 
header('Content-Type: application/json; charset=utf-8');
if (!function_exists('valasz')) {
    function valasz(array $adat, int $statusz = 200): void
    {
        http_response_code($statusz);
        echo json_encode($adat, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('getMezo')) {
    function getMezo(array $bemenet, string $nev): string
    {
        $ertek = $bemenet[$nev] ?? '';

        return is_string($ertek) || is_numeric($ertek)
            ? trim((string) $ertek)
            : '';
    }
}

if (!function_exists('getListaMezo')) {
    function getListaMezo(array $bemenet, string $nev): array
    {
        $ertekek = $bemenet[$nev] ?? [];

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

        return array_values(array_unique($lista));
    }
}

if (!function_exists('bontKeresesiSzavakra')) {
    function bontKeresesiSzavakra(string $keresettSzo): array
    {
        $szavak = preg_split('/\s+/u', trim($keresettSzo)) ?: [];
        return array_values(array_filter($szavak));
    }
}

if (!defined('PHPUNIT_TEST')) {
    header('Content-Type: application/json; charset=utf-8');

    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        valasz([
            'siker' => false,
            'hiba' => 'Csak GET és POST kérés engedélyezett.'
        ], 405);
    }

    require_once __DIR__ . '/dbconnect.php';

    $bemenet = array_merge($_POST, $_GET);

    $anyagok = getListaMezo($bemenet, 'anyag');
    $mintak = getListaMezo($bemenet, 'minta');

    $szurok = [
        'marka' => getMezo($bemenet, 'marka'),
        'modell' => getMezo($bemenet, 'modell'),
        'szin' => getMezo($bemenet, 'szin'),
    ];

    $keresettSzo = getMezo($bemenet, 'kereso');

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

    $keresesiSzavak = bontKeresesiSzavakra($keresettSzo);

    $lekerdezTermekeket = static function (
        array $szurok,
        array $anyagok,
        array $mintak,
        array $szavak
    ) use ($dbConn): array {
        $feltetelek = [];
        $parameterek = [];

        $szuroOszlopok = [
            'marka' => 'telefonmodell.marka',
            'modell' => 'telefonmodell.modell_nev',
            'szin' => 'valtozat.szin',
        ];

        foreach ($szuroOszlopok as $kulcs => $oszlop) {
            if ($szurok[$kulcs] !== '') {
                $feltetelek[] = $oszlop . ' = :szuro_' . $kulcs;
                $parameterek['szuro_' . $kulcs] = $szurok[$kulcs];
            }
        }

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

        foreach ($szavak as $index => $szo) {
            $nevParam = ':nev_' . $index;
            $mintaParam = ':minta_szo_' . $index;
            $modellParam = ':modell_szo_' . $index;
            $markaParam = ':marka_szo_' . $index;

            $feltetelek[] = '(termek.nev LIKE ' . $nevParam
                . ' OR termek.minta LIKE ' . $mintaParam
                . ' OR telefonmodell.modell_nev LIKE ' . $modellParam
                . ' OR telefonmodell.marka LIKE ' . $markaParam . ')';

            $keresesiMinta = '%' . $szo . '%';
            $parameterek['nev_' . $index] = $keresesiMinta;
            $parameterek['minta_szo_' . $index] = $keresesiMinta;
            $parameterek['modell_szo_' . $index] = $keresesiMinta;
            $parameterek['marka_szo_' . $index] = $keresesiMinta;
        }

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

        if ($feltetelek !== []) {
            $sql .= "\nWHERE " . implode("\n  AND ", $feltetelek);
        }

        $sql .= "\nORDER BY termek.nev, telefonmodell.marka, telefonmodell.modell_nev, valtozat.szin";

        $lekerdezes = $dbConn->prepare($sql);
        $lekerdezes->execute($parameterek);

        return $lekerdezes->fetchAll(PDO::FETCH_ASSOC);
    };

    try {
        $termekek = $lekerdezTermekeket($szurok, $anyagok, $mintak, $keresesiSzavak);
        $uzenet = '';

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

        valasz([
            'oldalak_szama' => $oldalakSzama,
            'uzenet' => $uzenet,
            'opciok' => $opciok,
            'siker' => true,
            'talalatok' => $termekek,
            'uzenet' => $uzenet,
            'opciok' => $opciok,
        ]);
    } catch (Throwable $hiba) {
        error_log('Terméklekérdezési hiba: ' . $hiba->getMessage());

        valasz([
            'siker' => false,
            'hiba' => 'A termékek lekérése nem sikerült.'
        ], 500);
    }
}
?>
