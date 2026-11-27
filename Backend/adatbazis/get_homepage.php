<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/dbconnect.php';

try {
    // Márkánként a legújabb képpel rendelkező termékváltozat képét adjuk vissza
    $markaLekerdezes = $dbConn->query(<<<'SQL'
SELECT
    markak.marka,
    (
        SELECT valtozat.kep_url
        FROM telefonmodellek AS modell
        INNER JOIN termek_valtozat AS valtozat
            ON valtozat.modell_id = modell.modell_id
        INNER JOIN termekek AS termek
            ON termek.termek_id = valtozat.termek_id
        WHERE modell.marka = markak.marka
          AND modell.aktiv = 1
          AND valtozat.aktiv = 1
          AND termek.aktiv = 1
          AND valtozat.kep_url IS NOT NULL
          AND valtozat.kep_url <> ''
          AND valtozat.kep_url <> 'placeholder.jpg'
        ORDER BY termek.termek_id DESC, valtozat.valtozat_id DESC
        LIMIT 1
    ) AS kep_url
FROM (
    SELECT DISTINCT marka
    FROM telefonmodellek
    WHERE aktiv = 1
) AS markak
ORDER BY markak.marka
SQL
    );
    $markak = $markaLekerdezes->fetchAll(PDO::FETCH_ASSOC);

    // A legnagyobb termek_id jelöli a legújabban felvitt termékeket
    $termekLekerdezes = $dbConn->query(<<<'SQL'
SELECT
    termek.termek_id,
    termek.nev AS termek_nev,
    MIN(valtozat.ar) AS ar,
    (
        SELECT kepValtozat.kep_url
        FROM termek_valtozat AS kepValtozat
        WHERE kepValtozat.termek_id = termek.termek_id
          AND kepValtozat.aktiv = 1
        ORDER BY
            CASE
                WHEN kepValtozat.kep_url IS NULL
                  OR kepValtozat.kep_url = ''
                  OR kepValtozat.kep_url = 'placeholder.jpg'
                THEN 1
                ELSE 0
            END,
            kepValtozat.valtozat_id DESC
        LIMIT 1
    ) AS kep_url
FROM termekek AS termek
INNER JOIN termek_valtozat AS valtozat
    ON valtozat.termek_id = termek.termek_id
WHERE termek.aktiv = 1
  AND valtozat.aktiv = 1
GROUP BY termek.termek_id, termek.nev
ORDER BY termek.termek_id DESC
LIMIT 4
SQL
    );
    $legujabbTermekek = $termekLekerdezes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'siker' => true,
        'markak' => $markak,
        'legujabb_termekek' => $legujabbTermekek,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $hiba) {
    error_log('Főoldali adatok lekérési hiba: ' . $hiba->getMessage());
    http_response_code(500);
    echo json_encode([
        'siker' => false,
        'hiba' => 'A főoldali adatok betöltése nem sikerült.',
    ], JSON_UNESCAPED_UNICODE);
}
?>