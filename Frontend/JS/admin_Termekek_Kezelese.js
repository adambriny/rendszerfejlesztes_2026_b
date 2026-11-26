// Az admin termékkezelő PHP-végpontja
const adminTermekApi = '../../Backend/adatbazis/admin_products.php';

// Itt tároljuk a szervertől kapott termékeket és modelladatokat
let adminTermekek = [];
let adminModellek = [];

// Ezek jelzik, hogy meglévő termékváltozatot szerkesztünk-e
let szerkesztettTermekId = null;
let szerkesztettValtozatId = null;

// Rövid üzenetet jelenít meg az admin felületen
function adminUzenetMegjelenitese(szoveg, hiba = false) {
    const uzenet = document.getElementById('adminUzenet');
    uzenet.className = 'ures-talalat';
    if (!uzenet) {
        return;
    }

    uzenet.textContent = szoveg;
    uzenet.style.color = hiba ? 'red' : '';
}

// Üzenetet ír ki az oldalsávban a rendelés vagy jelszógomb alatt
function adminMenuUzenetMegjelenitese(szoveg) {
    const uzenet = document.getElementById('adminMenuUzenet');

    if (uzenet) {
        uzenet.textContent = szoveg;
    }
}

// Lenullázza a lenyíló mező tartalmát, és betölti az új opciókat
function selectFeltolt(selectId, alapSzoveg, ertekek, ertekFuggveny, cimkeFuggveny) {
    const select = document.getElementById(selectId);

    if (!select) {
        return;
    }

    const alapOpcio = document.createElement('option');
    alapOpcio.value = '';
    alapOpcio.textContent = alapSzoveg;
    select.replaceChildren(alapOpcio);

    ertekek.forEach(ertek => {
        const opcio = document.createElement('option');
        opcio.value = ertekFuggveny(ertek);
        opcio.textContent = cimkeFuggveny(ertek);
        select.appendChild(opcio);
    });
}

// A kiválasztott márkához tartozó modelleket tölti be
function modellOpcioiBetoltese(marka, kivalasztottModellId = '') {
    const select = document.getElementById('termekModell');

    if (!select) {
        return;
    }

    const markahozTartozoModellek = adminModellek.filter(
        modell => marka !== '' && modell.marka === marka
    );

    selectFeltolt(
        'termekModell',
        marka ? 'Válassz modellt' : 'Előbb válassz márkát',
        markahozTartozoModellek,
        modell => modell.modell_id,
        modell => modell.modell_nev
    );

    if (kivalasztottModellId !== '') {
        select.value = String(kivalasztottModellId);
    }
}

// Betölti a terméklistát és az adatbázisból a választható értékeket
async function adminTermekekBetoltese() {
    try {
        const valasz = await fetch(adminTermekApi, {
            credentials: 'same-origin',
        });

        if (valasz.status === 401) {
            window.location.href = 'admin_Bejelentkezes.html';
            return;
        }

        const adat = await valasz.json();

        if (!valasz.ok || !adat.siker) {
            throw new Error(adat.hiba || 'A termékek betöltése nem sikerült.');
        }

        adminTermekek = adat.termekek || [];
        adminModellek = adat.opciok?.modellek || [];

        const markak = [...new Set(adminModellek.map(modell => modell.marka))]
            .sort((a, b) => a.localeCompare(b, 'hu'));

        // Betöltjük az admin lista márkaszűrőjét
        selectFeltolt(
            'markaSzuro',
            'Márka: Összes',
            markak,
            marka => marka,
            marka => marka
        );

        // Betöltjük az új vagy szerkesztett termék űrlapjának opcióit
        selectFeltolt(
            'termekMarka',
            'Válassz márkát',
            markak,
            marka => marka,
            marka => marka
        );

        modellOpcioiBetoltese('');

        selectFeltolt(
            'termekAnyag',
            'Válassz anyagot',
            adat.opciok?.anyagok || [],
            anyag => anyag,
            anyag => anyag
        );

        selectFeltolt(
            'termekSzin',
            'Válassz színt',
            adat.opciok?.szinek || [],
            szin => szin,
            szin => szin
        );

        selectFeltolt(
            'termekMinta',
            'Válassz mintát',
            adat.opciok?.mintak || [],
            minta => minta,
            minta => minta
        );

        termekListaMegjelenitese();
        adminUzenetMegjelenitese('');
    } catch (hiba) {
        console.error('Admin termékbetöltési hiba:', hiba);
        adminUzenetMegjelenitese(hiba.message, true);
    }
}

// A kereső és a márkaválasztó alapján szűri, majd megjeleníti a termékeket
function termekListaMegjelenitese() {
    const lista = document.getElementById('termekLista');

    if (!lista) {
        return;
    }

    lista.replaceChildren();

    if (adminTermekek.length === 0) {
        const uzenet = document.createElement('p');
        uzenet.className = 'ures-talalat';
        uzenet.textContent = 'Nincs megjeleníthető termék.';
        lista.appendChild(uzenet);
        return;
    }

    // Kiolvassuk a keresőmező és a márkaválasztó értékét
    const kereso = document.getElementById('keresoInput')?.value
        .trim()
        .toLocaleLowerCase('hu') || '';
    const keresesiSzavak = kereso.split(/\s+/u).filter(Boolean);
    const marka = document.getElementById('markaSzuro')?.value || '';

    // Először leszűrjük a termékeket a megadott feltételek alapján
    const szurtTermekek = adminTermekek.filter(termek => {
        // A vendégkeresőhöz hasonlóan minden keresőszót külön vizsgálunk
    const keresesiMezok = [
        termek.termek_nev,
        termek.minta,
        termek.modell_nev,
        termek.marka,
        termek.szin,
        termek.anyag
    ].map(ertek => String(ertek ?? '').toLocaleLowerCase('hu'));

    const keresesiTalalat = keresesiSzavak.every(szo =>
        keresesiMezok.some(mezo => mezo.includes(szo)));
    const markaTalalat = marka === '' || termek.marka === marka;

    return keresesiTalalat && markaTalalat;
    });

    // Jelezzük, ha nincs a feltételeknek megfelelő termék
    if (szurtTermekek.length === 0) {
        const uzenet = document.createElement('p');
        uzenet.className = 'ures-talalat';
        uzenet.textContent = 'Nincs a keresési feltételeknek megfelelő termék.';
        lista.appendChild(uzenet);
        return;
    }

    // A negyedik találattól kezdődő termékek lenyitható részbe kerülnek
    const tovabbiTermekek = document.createElement('details');
    tovabbiTermekek.id = 'tovabbiTermekek';

    const cim = document.createElement('summary');
    cim.textContent = `További termékek (${Math.max(0, szurtTermekek.length - 3)})`;
    tovabbiTermekek.appendChild(cim);

    szurtTermekek.forEach((termek, index) => {
        const sor = document.createElement('div');
        sor.className = 'termek-sor';
        sor.dataset.nev = termek.termek_nev;
        sor.dataset.marka = termek.marka;

        const infok = document.createElement('div');
        infok.className = 'termek-infok';

        const kepJelolo = document.createElement('span');
        kepJelolo.className = 'kep-jelolo';
        kepJelolo.textContent = '[Kép]';

        const leiras = document.createElement('span');
        leiras.textContent =
            `${termek.termek_nev} - ${termek.marka} ${termek.modell_nev} - `
            + `${termek.szin} - ${Number(termek.ar).toLocaleString('hu-HU')} Ft - `
            + `${termek.keszlet} db`;

        infok.append(kepJelolo, leiras);

        const muveletek = document.createElement('div');
        muveletek.className = 'termek-muveletek';

        const szerkesztesGomb = document.createElement('button');
        szerkesztesGomb.type = 'button';
        szerkesztesGomb.className = 'gomb-kerek gomb-szekunder';
        szerkesztesGomb.textContent = 'Szerkesztés';
        szerkesztesGomb.addEventListener('click', () => {
            termekSzerkesztes(termek);
        });

        const torlesGomb = document.createElement('button');
        torlesGomb.type = 'button';
        torlesGomb.className = 'gomb-kerek gomb-torles';
        torlesGomb.textContent = 'Törlés';
        torlesGomb.addEventListener('click', () => {
            termekTorles(termek.valtozat_id);
        });

        muveletek.append(szerkesztesGomb, torlesGomb);
        sor.append(infok, muveletek);

        // Az első három szűrt találat közvetlenül a listában jelenik meg
        if (index < 3) {
            lista.appendChild(sor);
        } else {
            tovabbiTermekek.appendChild(sor);
        }
    });

    // Csak akkor jelenítjük meg, ha háromnál több találat van
    if (szurtTermekek.length > 3) {
        lista.appendChild(tovabbiTermekek);
    }
}

// Megnyit egy adatbázisból betöltött változatot szerkesztésre
function termekSzerkesztes(termek) {
    szerkesztettTermekId = Number(termek.termek_id);
    szerkesztettValtozatId = Number(termek.valtozat_id);

    document.getElementById('formCim').textContent = 'Termék szerkesztése';
    document.getElementById('termekNev').value = termek.termek_nev;
    document.getElementById('termekAr').value = Number(termek.ar);
    document.getElementById('termekKeszlet').value = Number(termek.keszlet);
    document.getElementById('termekAnyag').value = termek.anyag;
    document.getElementById('termekSzin').value = termek.szin;
    document.getElementById('termekMinta').value = termek.minta;
    document.getElementById('termekMarka').value = termek.marka;

    // Előbb a márkához tartozó modelleket töltjük be, utána kiválasztjuk a modellt
    modellOpcioiBetoltese(termek.marka, termek.modell_id);

    document.getElementById('formSzekcio').scrollIntoView({ behavior: 'smooth' });
}

// Az űrlapot új termék felviteléhez előkészíti
function ujTermekLetrehozasa() {
    formUrites();

    document.getElementById('formCim').textContent = 'Új termék hozzáadása';
    document.getElementById('termekNev').value = '';
    document.getElementById('termekAr').value = '';
    document.getElementById('termekKeszlet').value = '0';

    document.getElementById('formSzekcio').scrollIntoView({ behavior: 'smooth' });
}

// Elküldi az új vagy módosított termék adatait a PHP-nak
async function termekMentese(event) {
    event.preventDefault();

    const adat = {
        muvelet: 'mentes',
        termek_id: szerkesztettTermekId,
        valtozat_id: szerkesztettValtozatId,
        nev: document.getElementById('termekNev').value.trim(),
        modell_id: Number(document.getElementById('termekModell').value),
        anyag: document.getElementById('termekAnyag').value,
        szin: document.getElementById('termekSzin').value,
        minta: document.getElementById('termekMinta').value,
        ar: Number(document.getElementById('termekAr').value),
        keszlet: Number(document.getElementById('termekKeszlet').value),
    };

    try {
        const valasz = await fetch(adminTermekApi, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(adat),
        });

        const eredmeny = await valasz.json();

        if (valasz.status === 401) {
            window.location.href = 'admin_Bejelentkezes.html';
            return;
        }

        if (!valasz.ok || !eredmeny.siker) {
            throw new Error(eredmeny.hiba || 'A mentés nem sikerült.');
        }

        adminUzenetMegjelenitese(eredmeny.uzenet);
        formUrites();
        await adminTermekekBetoltese();
    } catch (hiba) {
        console.error('Admin termékmentési hiba:', hiba);
        adminUzenetMegjelenitese(hiba.message, true);
    }
}

// Megerősítés után törli a kiválasztott termékváltozatot az adatbázisból
async function termekTorles(valtozatId) {
    if (!confirm('Biztosan törlöd ezt a termékváltozatot?')) {
        return;
    }

    try {
        const valasz = await fetch(adminTermekApi, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                muvelet: 'torles',
                valtozat_id: Number(valtozatId),
            }),
        });

        const eredmeny = await valasz.json();

        if (valasz.status === 401) {
            window.location.href = 'admin_Bejelentkezes.html';
            return;
        }

        if (!valasz.ok || !eredmeny.siker) {
            throw new Error(eredmeny.hiba || 'A törlés nem sikerült.');
        }

        adminUzenetMegjelenitese(eredmeny.uzenet);
        await adminTermekekBetoltese();
    } catch (hiba) {
        console.error('Admin terméktörlési hiba:', hiba);
        adminUzenetMegjelenitese(hiba.message, true);
    }
}

// Új termék módra állítja vissza az űrlapot
function formUrites() {
    szerkesztettTermekId = null;
    szerkesztettValtozatId = null;

    document.getElementById('termekForm').reset();
    document.getElementById('formCim').textContent = 'Új termék hozzáadása';

    modellOpcioiBetoltese('');
}

// A kereső vagy a márkaválasztó változásakor újrarajzolja a listát
function szuresFunkcio() {
    termekListaMegjelenitese();
}

// Kijelentkezteti az admint a szerveroldali munkamenetből
async function kijelentkezes() {
    try {
        await fetch('../../Backend/adatbazis/admin_logout.php', {
            method: 'POST',
            credentials: 'same-origin',
        });
    } finally {
        window.location.href = 'admin_Bejelentkezes.html';
    }
}

// Betöltéskor ellenőrizzük az admin munkamenetet és lekérjük a termékeket
document.addEventListener('DOMContentLoaded', adminTermekekBetoltese);

// A márka kiválasztásakor a Modell listát az adott márkára szűkítjük
document.getElementById('termekMarka')
    ?.addEventListener('change', event => {
        modellOpcioiBetoltese(event.target.value);
    });

// A fejléc keresője is az admin terméklistáját szűri
const fejleciKereso = document.querySelector('.kereso-sav input');

fejleciKereso?.addEventListener('input', () => {
    const listaKereso = document.getElementById('keresoInput');

    if (listaKereso) {
        listaKereso.value = fejleciKereso.value;
        szuresFunkcio();
    }
});