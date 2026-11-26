
# DEVELOPMENT.md – Egyedi Telefontok Webáruház

Ez a dokumentum a fejlesztői csapat közös megállapodásait, a helyi környezet teljes körű beállítását és tesztelését, a gyakori fejlesztői feladatok megoldási receptjeit (Cookbook), valamint a projekt legfontosabb architekturális döntéseit (ADR) rögzíti.

**Projekt státusz (3. Mérföldkő):** Az adatbázis-séma, a felhasználói regisztráció/bejelentkezés, a dinamikus termékszűrés és a Session-alapú kosárkezelés elkészült. A megrendelési folyamat véglegesítése (checkout) és az adminisztrátori felület teljes körű CRUD funkciói a 4. mérföldkőben kerülnek átadásra.


---

## 1. Csapatmegállapodások és "Definition of Done" (DoD)

### Kódolási és biztonsági alapelvek
* *Technológiai stack:* PHP 8.0+, MySQL 8.0 / MariaDB (utf8mb4_hungarian_ci karakterkódolás), HTML5, CSS3, JavaScript, Bootstrap 5.
* *SQL Injection védelem:* Tilos a nyers SQL karakterláncok összefűzése felhasználói inputokkal. Minden adatbázis-művelethez kötelező a *Prepared Statements* és a paraméter-hozzákötés használata.
* *XSS védelem:* Minden felhasználói inputot és adatbázisból érkező szöveget (nevek, címek, termékleírások) a frontend felé történő kiíratás előtt kötelező átfuttatni a htmlspecialchars() függvényen.
* *Jelszókezelés:* Jelszavakat kizárólag a password_hash($jelszo, PASSWORD_BCRYPT) függvénnyel tárolunk, és a password_verify() függvénnyel ellenőrzünk.
* *Logikai törlés:* A termékek, telefonmodellek és felhasználók törlése az aktiv (BOOLEAN) mező FALSE értékre állításával történik.

### Definition of Done (Mikor kész egy feladat?)
1. A kód megfelel a biztonsági (Prepared Statements, htmlspecialchars, jelszó-hashelés) előírásoknak.
2. A felület reszponzív módon működik mobilon, tableten és asztali nézetben (Bootstrap 5).
3. A jogosultsági szintek (vasarlo vs. admin) megfelelően védik az adminisztrátori funkciókat.
4. A módosítások helyi XAMPP környezetben tesztelve lettek, és felkerültek a Git verziókövetőbe.

---

## 2. Helyi fejlesztői környezet beállítása és futtatása (VSC + XAMPP)

Ez az útmutató végigvezet a projekt letöltésén a GitHub repóból, a XAMPP és az adatbázis beállításán, valamint a Visual Studio Code-ban való `F5`-ös elindításon.

### 2.1. Előfeltételek telepítése
Mielőtt elkezded, győződj meg róla, hogy telepítve vannak a gépeden:
* *XAMPP* (PHP 8.0+ és MySQL / MariaDB csomaggal)
* *Visual Studio Code (VSC)*

---

### 2.2. A projekt letöltése és kicsomagolása

1. *Letöltés ZIP-ként:* Nyisd meg a [GitHub repót](https://github.com), kattints a zöld **<> Code** gombra, majd válaszd a **Download ZIP** opciót.
2. *Kicsomagolás a XAMPP mappájába:* 
   * Csomagold ki a letöltött rendszerfejlesztes_2026_b-main.zip fájlt közvetlenül a XAMPP webes gyökérkönyvtárába:
     C:\xampp\htdocs\
   * Nevezd át a kicsomagolt mappát **rendszerfejlesztes_2026_b** névre (így a teljes elérési út: C:\xampp\htdocs\rendszerfejlesztes_2026_b).

---

### 2.3. XAMPP, .env és az Adatbázis beállítása

1. *XAMPP szolgáltatások elindítása:*
   * Nyisd meg a *XAMPP Control Panel* programot.
   * Kattints a *Start* gombra az *Apache* sorában.
   * Kattints a *Start* gombra a *MySQL* sorában.
   * Várd meg, amíg mindkét modul neve *zöld* hátterűre vált.
2. *Adatbázis importálása a repóból:*
   * Nyisd meg a böngészőben a phpMyAdmin felületet: http://localhost/phpmyadmin
   * Kattints a felső menüsorban az *Importálás (Import)* fülre.
   * Kattints a *Fájl kiválasztása* gombra, és tallózd be a projekt **Backend/adatbazis/** mappájában található .sql fájlt, majd kattints az oldal alján az *Importálás (Go)* gombra.
   * Bal oldalon meg kell jelennie a telefonwebshop adatbázisnak a 6 táblával (felhasznalok, telefonmodellek, termekek, termek_valtozat, rendelesek, rendeles_tetelek).
3. **Környezeti változók (.env) ellenőrzése:**
   * Nyisd meg a projekt gyökerében található .env fájlt, és ellenőrizd, hogy a helyi XAMPP adatbázis-adataid szerepelnek-e benne (DB_HOST=localhost, DB_NAME=TelefonWebshop, DB_USER=root, DB_PASS= üresen).

---

### 2.4. Megnyitás Visual Studio Code-ban és indítás `F5`-tel

1. *Mappa megnyitása VSC-ben:*
   * Indítsd el a **Visual Studio Code**-ot.
   * Kattints a *File > Open Folder...* menüpontra, és válaszd ki a C:\xampp\htdocs\rendszerfejlesztes_2026_b mappát.
2. **Indítási konfiguráció (.vscode/launch.json) létrehozása az `F5`-höz:**
   * Hozz létre a projekt gyökerében egy .vscode mappát, és azon belül egy launch.json fájlt az alábbi tartalommal:

{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Telefontok Webshop Indítása (XAMPP)",
            "type": "chrome",
            "request": "launch",
            "url": "http://localhost/rendszerfejlesztes_2026_b/index.html",
            "webRoot": "\${workspaceFolder}"
        }
    ]
}
*(Megjegyzés: Ha Microsoft Edge-et használsz, a "type": "msedge" értéket írd be. Mivel a projekt PHP backendet használ, **ne** a VSC "Live Server" bővítményével indítsd el, hanem mindig ezen a localhost címen keresztül.)*

3. *Program futtatása:*
   * Nyomd meg az **F5** billentyűt a VSC-ben!
   * A böngésző automatikusan megnyílik a webáruház főoldalán (http://localhost/rendszerfejlesztes_2026_b/index.html).

---

### 2.5. Jelenlegi működés ellenőrzése (3. Mérföldkő Smoke Test)

A sikeres telepítés után az alábbi, már elkészült funkciókat tudod letesztelni:
1. *Adatbázis-kapcsolat ellenőrzése:* Nyisd meg a főoldalt, és ellenőrizd, hogy a termékek/kategóriák hibaüzenet nélkül betöltődnek-e az adatbázisból.
2. *Regisztráció és belépés:* Hozz létre egy új felhasználói fiókot a felületen, majd jelentkezz be vele. *(A phpMyAdmin felhasznalok táblájában ellenőrizheted, hogy a jelszó BCRYPT hash-ként mentődött-e el.)*
3. *Termékszűrés tesztelése:* Szűrj rá különböző telefonmárkákra, modellekre, színekre vagy anyagokra, és ellenőrizd a találati lista frissülését.
4. *Session kosár tesztelése:* Helyezz termékeket a kosárba, majd a Kosár nézetben módosítsd a darabszámokat vagy törölj ki egy tételt.

(Megjegyzés: A megrendelés leadása és az adminisztrátori felület teljes körű tesztelése a 4. mérföldkő lezárása után lesz elérhető.)

---

## 3. Fejlesztői Cookbook / HOWTO receptek

### Recept 1: Session-alapú kosárkezelés
A kosár a $_SESSION['kosar'] tömbben fut (valtozat_id => mennyiseg):

session_start();
\(valtozat_id = (int)\)_POST['valtozat_id'];
mennyiseg = (int)_POST['mennyiseg'];

if (!isset(\(_SESSION['kosar'])) {\)_SESSION['kosar'] = [];
}
\$_SESSION['kosar'][\(valtozat_id] = (\)_SESSION['kosar'][\(valtozat_id] ?? 0) +\)mennyiseg;

### Recept 2: Rendelés mentése adatbázis-tranzakcióval (4. mérföldkő sablon)
A rendelés véglegesítésekor tranzakciót használunk a készlet-inkonzisztencia elkerülésére:

try {
    \$pdo->beginTransaction();

    stmt = pdo->prepare("INSERT INTO rendelesek (felhasznalo_id, rendeles_datuma, statusz, szallitasi_adatok, szamlazasi_adatok) VALUES (?, NOW(), 'leadva', ?, ?)");
    \(stmt->execute([\)userId, szallitasiAdatok, szamlazasiAdatok]);
    rendelesId = pdo->lastInsertId();

    foreach (\$_SESSION['kosar'] as valtozatId => mennyiseg) {
        arStmt = pdo->prepare("SELECT ar, keszlet FROM termek_valtozat WHERE valtozat_id = ? FOR UPDATE");
        \(arStmt->execute([\)valtozatId]);
        termek = arStmt->fetch();

        if (!\$termek || \(termek['keszlet'] <\)mennyiseg) {
            throw new Exception("Nincs elegendő raktárkészlet.");
        }

        tetelStmt = pdo->prepare("INSERT INTO rendeles_tetelek (rendeles_id, valtozat_id, mennyiseg, egysegar) VALUES (?, ?, ?, ?)");
        \$tetelStmt->execute([rendelesId, valtozatId, mennyiseg, termek['ar']]);

        keszletStmt = pdo->prepare("UPDATE termek_valtozat SET keszlet = keszlet - ? WHERE valtozat_id = ?");
        \$keszletStmt->execute([mennyiseg, valtozatId]);
    }

    \$pdo->commit();
    unset(\$_SESSION['kosar']);
} catch (Exception e) pdo->rollBack();
}