<?php
// Ezt a fájlt csak parancssorból/terminálból szabad futtatni
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("A fájl csak parancssorból futtatható.\n");
}

require_once __DIR__ . '/dbconnect.php';

// Bekérünk egy értéket a parancssorból
function beker(string $uzenet)
{
    echo $uzenet;
    return trim((string) fgets(STDIN));
}

$nev = beker('Admin neve: ');
$email = beker('Admin e-mail címe: ');
$jelszo = beker('Admin jelszava: ');

// Megszámoljuk a jelszó karaktereit az ékezetes betűket is kezelve.
preg_match_all('/./us', $jelszo, $jelszoKarakterek);
$jelszoHossz = count($jelszoKarakterek[0]);

// Ellenőrizzük, hogy minden kötelező karaktertípus szerepel-e benne
$vanKisbetu = preg_match('/\p{Ll}/u', $jelszo) === 1;
$vanNagybetu = preg_match('/\p{Lu}/u', $jelszo) === 1;
$vanSzam = preg_match('/\p{N}/u', $jelszo) === 1;
$vanKulonlegesKarakter = preg_match('/[\p{P}\p{S}]/u', $jelszo) === 1;

if (
    $nev === ''
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || $jelszoHossz < 8
    || !$vanKisbetu
    || !$vanNagybetu
    || !$vanSzam
    || !$vanKulonlegesKarakter
) {
    exit(
        "Hibás adat. Az e-mail legyen érvényes; "
        . "a jelszó legalább 8 karakterből álljon, "
        . "és tartalmazzon kisbetűt, nagybetűt, számot és különleges karaktert.\n"
    );
}

// Megnézzük van-e már ilyen e-mail-című felhasználó
$ellenorzes = $dbConn->prepare(
    'SELECT felhasznalo_id FROM felhasznalok WHERE email = :email'
);
$ellenorzes->execute(['email' => $email]);

if ($ellenorzes->fetch()) {
    exit("Ezzel az e-mail-címmel már létezik felhasználó.\n");
}

// A jelszót hash-elve tároljuk, nem olvasható szövegként
$jelszoHash = password_hash($jelszo, PASSWORD_DEFAULT);

$beszuras = $dbConn->prepare(
    'INSERT INTO felhasznalok (nev, email, jelszo_hash, szerepkor, aktiv)
     VALUES (:nev, :email, :jelszo_hash, :szerepkor, 1)'
);

$beszuras->execute([
    'nev' => $nev,
    'email' => $email,
    'jelszo_hash' => $jelszoHash,
    'szerepkor' => 'admin',
]);

echo "Az adminfiók létrejött.\n";