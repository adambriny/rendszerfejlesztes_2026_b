# Rendszerfejlesztés 2026 B

# Telefontok Webshop Projektterv 2026

## 1. Összefoglaló

Projektünk célja egy egyedi telefontokokat értékesítő webáruház megtervezése és elkészítése. A weboldal segítségével a vásárlók egyszerűen és gyorsan találhatnak a telefonjukhoz megfelelő, különböző stílusú és kialakítású telefontokokat. A webshopban többféle telefontípushoz és modellhez kínálunk termékeket, amelyek között szín, minta, anyag és kialakítás alapján is lehet választani.

A projekt során fontos szempont a könnyű kezelhetőség, az átlátható felépítés és a modern, egységes megjelenés. A webalkalmazás célja egy valódi webáruház alapvető működésének bemutatása, amely lehetővé teszi a termékek böngészését, kiválasztását, kosárba helyezését és a vásárlási folyamat végigvezetését.

## 2. A projekt bemutatása

Ez a projektterv egy telefontokokat árusító webáruház fejlesztését mutatja be, amely 2026. szeptember 10-től novemberéig tart. A projekten három fejlesztő, Jánk Titanilla, Daróczi Attila és Bíró Linda fog dolgozni. A projekt célja egy működőképes, könnyen használható és átlátható weboldal elkészítése, amelyen a felhasználók különböző telefonmodellekhez választhatnak és vásárolhatnak telefontokokat. Az elkészült feladatokat négy alkalommal, mérföldkövenként fogjuk bemutatni annak érdekében, hogy a projekt folyamatos előrehaladását és az egyes fejlesztési szakaszok eredményeit is nyomon lehessen követni.

## 2.1. A webáruház célja

A rendszernek képesnek kell lennie arra, hogy az egyedi telefontokokat, valamint azok adatait, például a telefonmárkát, telefonmodellt, árat, színt, anyagot, mintát és készletet nyilvántartsa annak érdekében, hogy a vásárlók egyszerűen megtalálhassák és kiválaszthassák a telefonjukhoz megfelelő terméket. Ezenkívül a rendszernek kezelnie kell a felhasználói fiókokat, a vásárlói adatokat, valamint a leadott rendeléseket és azok állapotát. A vásárlók különböző szempontok alapján kereshetnek és szűrhetnek a telefontokok között, majd a kiválasztott termékeket a kosárba helyezhetik és megrendelhetik. A rendszer a rendelés során kiszámolja a fizetendő végösszeget, valamint megjeleníti a rendeléshez szükséges adatokat. Az adminisztrátorok számára lehetőség van a termékek, készletek, árak és rendelések kezelésére, valamint új termékek hozzáadására és meglévő termékek módosítására vagy törlésére. Minden funkció a megfelelő felhasználói jogosultság mellett használható, így az egyes adatok a felhasználó jogosultságától függően írhatók, olvashatók vagy nem tekinthetők meg.

### 2.2. Funkcionális követelmények

* **Felhasználók kezelése (látogató, regisztrált vásárló, admin) (CRUD)**
* **Felhasználói munkamenet megvalósítása (regisztráció, bejelentkezés, kijelentkezés)**
* **Termékek kezelése és adminisztrációja (CRUD)**
* **Termékek keresése kulcsszavak alapján**
* **Kosár (Cart) funkció kezelése (termékek hozzáadása, módosítása, törlése)**
* **Vásárlások/Rendelések lebonyolítása és kezelése (CRUD)**

### 2.3. Nem funkcionális követelmények

* **A kliens oldal böngészőfüggetlen legyen**
* **Reszponzív megjelenés (mobil, tablet és asztali nézet)**
* **Az érzékeny adatokat (például jelszavak, felhasználói adatok) biztonságosan, titkosítva tároljuk**
* **Gyors oldalbetöltési idő és optimalizált adatbázis-lekérdezések a gördülékeny vásárlási élményért**
* **A legfrissebb technológiákat használja a rendszer**

## 4. Szervezeti felépítés és felelősségmegosztás

A projekt megrendelője `Tóth Attila`. A `Webshop` projektet a projektcsapat fogja végrehajtani, ami `jelenleg három főből áll. A csapatban pályakezdő programozók vannak, de elhivatott, még gyakorlatra készülő diákok.`

### 4.1 Projektcsapat

A projekt a következő emberekből áll:

| Név          | Pozíció          |   E-mail cím (stud-os)        |
|--------------|------------------|-------------------------------|
| `Daroczi Attila` | Projekt tag  | `ezprivatinfo@nemtudjukhogymegekellmondani.com`    |
| `Bíró Linda` | Projekt tag      | `ezprivatinfo@nemtudjukhogymegekellmondani.com`    |
| `Jánk Titanilla`   | Projektmenedzser      | `ezprivatinfo@nemtudjukhogymegekellmondani.com`    |

## 5. A munka feltételei

### 5.1. Munkakörnyezet

A projekt a következő munkaállomásokat fogja használni a munka során:

 - `Munkaállomások: 3 db, Windows 11-es operációs rendszerrel`
 - `ASUS ROG ZEPHYRUS G14 Laptop (CPU: AMD Ryzen 9 5600, RAM: 40 GB, GPU: NVIDIA GeForce RTX 3060)`
 - `Asztali gép (CPU: AMD Ryzen 5 5500, RAM: 16 GB, GPU: NVIDIA GeForce GTX 1650)`
 - `Asztali gép (CPU: AMD Ryzen 5 5600, RAM: 16 GB, GPU: AMD Radeon(TM))`

A projekt a következő technológiákat/szoftvereket fogja használni a munka során: 

 - `Git verziókövető`
 - `Visual Studio Code`
 - `HTML5, CSS3, JavaScript / PHP technológiák`
 - `XAMPP / MySQL adatbázis-kezelő környezet`

### 5.2. Rizikómenedzsment

| Kockázat | Leírás | Valószínűség | Hatás |
| :--- | :--- | :--- | :--- |
| `Betegség` | `Hátráltatja munkavégzőt és a munkavégzést, így az egész projektre kihatással van. Megoldás: a feladatok átrendezése / csoportosítása.` | `nagy` | `erős` |
| `Kommunikációs fennakadás a csapattagokkal` | `A csapattagok között nem elégséges az információáramlás, nem pontosan, esetleg késve vagy nem egyértelműen tájékoztatjuk egymást. Megoldás: még gyakoribb megbeszélések és ellenőrzések.` | `kis` | `erős` |
| `Szoftver- / Hardverhiba` | `Az eszközök meghibásodása vagy adatvesztés a fejlesztés során. Megoldás: rendszeres kódmentés a Git verziókövetőbe és felhőalapú tárolás használata.` | `kis` | `erős` |
| `Extrém ZH / vizsgaidőszak` | `A tagok leterheltsége az egyetemi vizsgák miatt, ami lassíthatja a projekt ütemét. Megoldás: előre megtervezett ütemterv, feladatok ideális elosztása a vizsgák előtt.` | `nagy` | `közepes` |
| `Tag kiesése` | `Valamelyik csapattag váratlan tartós távolléte vagy kilépése. Megoldás: a kód és a dokumentáció folyamatos megosztása, hogy bárki át tudja venni a feladatokat.` | `kis` | `erős` |



