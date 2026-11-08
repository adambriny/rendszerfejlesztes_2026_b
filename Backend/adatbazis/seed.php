<?php
require_once __DIR__ . '/dbconnect.php';

//telefonmodellek tábla feltöltés

$telefonmodell= [
    ['marka'=>'Apple' ,'modell' => 'iPhone 15'],
    ['marka'=>'Apple' ,'modell' => 'iPhone 14'],
    ['marka'=>'Apple' ,'modell' => 'iPhone 13'],
    ['marka'=>'Apple' ,'modell' => 'iPhone 12'],
    ['marka'=>'Samsung' ,'modell' => 'Galaxy S24'],
    ['marka'=>'Samsung' ,'modell' => 'Galaxy S24 Ultra'],
    ['marka'=>'Samsung' ,'modell' => 'Galaxy S23'],
    ['marka'=>'Samsung' ,'modell' => 'Galaxy A54'],
    ['marka'=>'Samsung' ,'modell' => 'Galaxy A34'],
    ['marka'=>'Xiaomi' ,'modell' => 'Redmi Note 13'],
    ['marka'=>'Xiaomi' ,'modell' => 'Redmi 13T'],
    ['marka'=>'Huawei' ,'modell' => 'P60 Pro'],
    ['marka'=>'Google' ,'modell' => 'Pixel 8'],
    ['marka'=>'OnePlus' ,'modell' => 'OnePlus 12']
];

try{
    foreach($telefonmodell as $item){
        $sqlModellBeszur= "INSERT INTO telefonmodellek ( marka, modell_nev) VALUES (:marka , :modell_nev)";
        $lekerdezMod= $dbConn->prepare($sqlModellBeszur);
        $lekerdezMod->execute([
            ':marka' => $item['marka'],
            ':modell_nev' => $item['modell']
        ]);
    }
    echo "<p>Sikeresen hozzáadva!</p>";
}catch(PDOException $x)
{
    $hiba = "{$x->getCode()} - {$x->getMessage()} - {$x->getFile()}";
    error_log("[" . date("Y.m.d H:i:s") . "] $hiba".PHP_EOL, 3 ,"./error.log");
    echo "<p>Hiba történt az adatbázis műveleteknél </p>\n";
}

//termekek tábla feltöltés

$termekek = [
    [
        'nev' => 'Prémium Folyékony Szilikon Tok', 
        'leiras' => 'Puha tapintású, mikroszálas belsős szilikon tok, amely tökéletesen védi a telefont a karcolásoktól és ütésektől.', 
        'minta' => 'Egyszínű', 
        'anyag' => 'Szilikon'
    ],
    [
        'nev' => 'Elegáns Márvány mintás Tok', 
        'leiras' => 'Stílusos, fényes felületű márvány mintás védőtok stílusos megjelenést biztosítva.', 
        'minta' => 'Márvány', 
        'anyag' => 'Fa'
    ],
    [
        'nev' => 'Ultra Rugalmas Crystal Clear Tok', 
        'leiras' => 'Teljesen átlátszó, vékony tok, amely megőrzi a telefon eredeti dizájnját.', 
        'minta' => 'Átlátszó', 
        'anyag' => 'Szilikon'
    ],
    [
        'nev' => 'Carbon Hatású Ütésálló Páncél Tok', 
        'leiras' => 'Katonai szintű védelem a sarkokon megerősített ütközéssel és modern karbon mintával.', 
        'minta' => 'Karbon', 
        'anyag' => 'TPU'
    ],
    [
        'nev' => 'Prémium Bőr Hatású Védőtok', 
        'leiras' => 'Prémium textúrájú bőr hatású tok elegáns megjelenéssel és biztos fogással.', 
        'minta' => 'Texturált', 
        'anyag' => 'Bőr'
    ]
];

try{
    foreach($termekek as $item){
        $sqlTermekBeszur= "INSERT INTO termekek ( nev, leiras, minta, anyag) VALUES (:nev , :leiras, :minta, :anyag)";
        $lekerdezTerm= $dbConn->prepare($sqlTermekBeszur);
        $lekerdezTerm->execute([
            ':nev' => $item['nev'],
            ':leiras' => $item['leiras'],
            ':minta' => $item['minta'],
            ':anyag' => $item['anyag']
        ]);
    }
    echo "<p>Sikeresen hozzáadva!</p>";
}catch(PDOException $x)
{
    $hiba = "{$x->getCode()} - {$x->getMessage()} - {$x->getFile()}";
    error_log("[" . date("Y.m.d H:i:s") . "] $hiba".PHP_EOL, 3 ,"./error.log");
    echo "<p>Hiba történt az adatbázis műveleteknél </p>\n";
}

//termek_valtozat tábla feltöltés (termekmodellek és termekek össze kötése)

try{
    //termek id lekérés
    $sqlTermekId= "SELECT termek_id FROM termekek";
    $lekerdezTermekId= $dbConn->prepare($sqlTermekId);
    $lekerdezTermekId->execute();
    $termekId= $lekerdezTermekId->fetchAll(PDO::FETCH_ASSOC);

    //modell id lekérés
    $sqlModellId= "SELECT modell_id FROM telefonmodellek";
    $lekerdezModellId= $dbConn->prepare($sqlModellId);
    $lekerdezModellId->execute();
    $modellId= $lekerdezModellId->fetchAll(PDO::FETCH_ASSOC);

    //változat feltöltés
    $sqlValtozatBeszur = "INSERT INTO termek_valtozat (termek_id, modell_id, szin, ar, keszlet, kep_url) VALUES (:termek_id, :modell_id, :szin, :ar, :keszlet, :kep_url)";

    $lekerdezesValtozat = $dbConn->prepare($sqlValtozatBeszur);
    $szinek=["fekete","fehér","piros","átlátszó","rózsaszín","zöld","kék"];

    foreach($termekId as $t){
        foreach($modellId as $m){
            $lekerdezesValtozat->execute([
                ':termek_id' => $t['termek_id'],
                ':modell_id' => $m['modell_id'],
                ':szin'      => $szinek[array_rand($szinek)],
                ':ar'        => rand(2000, 7990), // Véletlenszerű ár 2000 és 7990 Ft között
                ':keszlet'   => rand(5, 25),      // Véletlenszerű raktárkészlet
                ':kep_url'   => 'placeholder.jpg'
            ]);
        }
    }
    echo "<p>Sikeresen hozzáadva!</p>";
}catch(PDOException $x)
{
    $hiba = "{$x->getCode()} - {$x->getMessage()} - {$x->getFile()}";
    error_log("[" . date("Y.m.d H:i:s") . "] $hiba".PHP_EOL, 3 ,"./error.log");
    echo "<p>Hiba történt az adatbázis műveleteknél </p>\n";
}
?>
