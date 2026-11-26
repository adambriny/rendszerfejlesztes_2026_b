<?php
// A válasz JSON-formátumú lesz
header('Content-Type: application/json; charset=utf-8');

// Egységes JSON-választ küldünk
function valasz(array $adat, int $statusz = 200): void
{
    http_response_code($statusz);
    echo json_encode($adat, JSON_UNESCAPED_UNICODE);
    exit;
}

// Csak POST kérés használható
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    valasz(['siker' => false, 'hiba' => 'Nem megfelelő kérés.'], 405);
}

// Beolvassuk a kért e-mail-címet
$bemenet = json_decode(file_get_contents('php://input'), true);
$email = is_array($bemenet) && is_string($bemenet['email'] ?? null)
    ? trim($bemenet['email'])
    : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    valasz(['siker' => false, 'hiba' => 'Adj meg érvényes e-mail-címet.'], 400);
}

require_once __DIR__ . '/dbconnect.php';

try {
    $felhasznaloLe = $dbConn->prepare(
        'SELECT felhasznalo_id
         FROM felhasznalok
         WHERE email = :email'
    );
    $felhasznaloLe->execute(['email' => $email]);
    $felhasznalo = $felhasznaloLe->fetch(PDO::FETCH_ASSOC);

    if (!$felhasznalo) {
        valasz([
            'siker' => false,
            'hiba' => 'Ezzel az e-mail-címmel nem található fiók.'
        ], 404);
    }

    // Kisbetűből, nagybetűből, számból és jelből is teszünk bele
    $kisbetuk = 'abcdefghijkmnopqrstuvwxyz';
    $nagybetuk = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $szamok = '23456789';
    $jelek = '!@#$%*-_+';

    $karakterek = [
        $kisbetuk[random_int(0, strlen($kisbetuk) - 1)],
        $nagybetuk[random_int(0, strlen($nagybetuk) - 1)],
        $szamok[random_int(0, strlen($szamok) - 1)],
        $jelek[random_int(0, strlen($jelek) - 1)],
    ];

    $osszesKarakter = $kisbetuk . $nagybetuk . $szamok . $jelek;

    while (count($karakterek) < 12) {
        $karakterek[] = $osszesKarakter[
            random_int(0, strlen($osszesKarakter) - 1)
        ];
    }

    // Véletlenszerű sorrendbe tesszük a karaktereket
    for ($i = count($karakterek) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        [$karakterek[$i], $karakterek[$j]] =
            [$karakterek[$j], $karakterek[$i]];
    }

    $ujJelszo = implode('', $karakterek);
    $ujHash = password_hash($ujJelszo, PASSWORD_DEFAULT);

    $jelszoMentes = $dbConn->prepare(
        'UPDATE felhasznalok
         SET jelszo_hash = :jelszo_hash
         WHERE felhasznalo_id = :id'
    );
    $jelszoMentes->execute([
        'jelszo_hash' => $ujHash,
        'id' => $felhasznalo['felhasznalo_id'],
    ]);

    // Iskolai szimulációban a generált jelszót a képernyőn mutatjuk meg
    valasz([
        'siker' => true,
        'uj_jelszo' => $ujJelszo,
    ]);
} catch (Throwable $hiba) {
    error_log('Jelszó-visszaállítási hiba: ' . $hiba->getMessage());
    valasz(['siker' => false, 'hiba' => 'Az új jelszó létrehozása nem sikerült.'], 500);
}
?>