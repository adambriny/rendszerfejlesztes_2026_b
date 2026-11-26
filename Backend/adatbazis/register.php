<?php
// A válasz JSON-formátumú lesz
header('Content-Type: application/json; charset=utf-8');

// Egységes JSON-választ küldünk, majd lezárjuk a kérést
function valasz(array $adat, int $statusz = 200): void
{
    http_response_code($statusz);
    echo json_encode($adat, JSON_UNESCAPED_UNICODE);
    exit;
}

// A regisztrációhoz csak POST kérés használható
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    valasz(['siker' => false, 'hiba' => 'Nem megfelelő kérés.'], 405);
}

// Beolvassuk a böngészőből érkező adatokat
$bemenet = json_decode(file_get_contents('php://input'), true);

if (!is_array($bemenet)) {
    $bemenet = $_POST;
}

$nev = isset($bemenet['nev']) && is_string($bemenet['nev'])
    ? trim($bemenet['nev'])
    : '';

$email = isset($bemenet['email']) && is_string($bemenet['email'])
    ? trim($bemenet['email'])
    : '';

$jelszo = isset($bemenet['jelszo']) && is_string($bemenet['jelszo'])
    ? $bemenet['jelszo']
    : '';

// A név nem lehet üres vagy csak számokból álló
if ($nev === '' || preg_match('/\p{L}/u', $nev) !== 1) {
    valasz(['siker' => false, 'hiba' => 'A név legalább egy betűt tartalmazzon.'], 400);
}

if (mb_strlen($nev, 'UTF-8') > 150) {
    valasz(['siker' => false, 'hiba' => 'A név legfeljebb 150 karakter lehet.'], 400);
}

// Ellenőrizzük az e-mail formátumát
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    valasz(['siker' => false, 'hiba' => 'Adj meg érvényes e-mail-címet.'], 400);
}

// A jelszónak meg kell felelnie a megadott szabályoknak
$jelszoHossza = mb_strlen($jelszo, 'UTF-8');
$vanKisbetu = preg_match('/\p{Ll}/u', $jelszo) === 1;
$vanNagybetu = preg_match('/\p{Lu}/u', $jelszo) === 1;
$vanSzam = preg_match('/\p{N}/u', $jelszo) === 1;
$vanKulonlegesKarakter = preg_match('/[\p{P}\p{S}]/u', $jelszo) === 1;

if (
    $jelszoHossza < 8
    || !$vanKisbetu
    || !$vanNagybetu
    || !$vanSzam
    || !$vanKulonlegesKarakter
) {
    valasz([
        'siker' => false,
        'hiba' => 'A jelszó legalább 8 karakter legyen, kis- és nagybetűvel, számmal és különleges karakterrel.'
    ], 400);
}

require_once __DIR__ . '/dbconnect.php';

try {
    // Megnézzük, hogy használja-e már valaki ezt az e-mail-címet
    $emailLe = $dbConn->prepare(
        'SELECT felhasznalo_id
         FROM felhasznalok
         WHERE email = :email'
    );
    $emailLe->execute(['email' => $email]);

    if ($emailLe->fetch()) {
        valasz(['siker' => false, 'hiba' => 'Ezzel az e-mail-címmel már van fiók.'], 409);
    }

    // Hash-elve mentjük a jelszót
    $jelszoHash = password_hash($jelszo, PASSWORD_DEFAULT);

    $mentes = $dbConn->prepare(
        'INSERT INTO felhasznalok (nev, email, jelszo_hash, szerepkor, aktiv)
         VALUES (:nev, :email, :jelszo_hash, :szerepkor, 1)'
    );
    $mentes->execute([
        'nev' => $nev,
        'email' => $email,
        'jelszo_hash' => $jelszoHash,
        'szerepkor' => 'vasarlo',
    ]);

    valasz([
        'siker' => true,
        'uzenet' => 'Sikeres regisztráció. Most már bejelentkezhetsz.',
    ]);
} catch (PDOException $hiba) {
    error_log('Regisztrációs adatbázishiba: ' . $hiba->getMessage());

    // Az adatbázis egyedi e-mail-korlátozása párhuzamos regisztrációnál is véd
    if ($hiba->getCode() === '23000') {
        valasz(['siker' => false, 'hiba' => 'Ezzel az e-mail-címmel már van fiók.'], 409);
    }

    valasz(['siker' => false, 'hiba' => 'A regisztráció nem sikerült.'], 500);
} catch (Throwable $hiba) {
    error_log('Regisztrációs hiba: ' . $hiba->getMessage());
    valasz(['siker' => false, 'hiba' => 'A regisztráció nem sikerült.'], 500);
}
?>