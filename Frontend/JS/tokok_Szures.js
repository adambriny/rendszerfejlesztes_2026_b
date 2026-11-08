// Élő készlet szűrés funkció
function szuresRaktar() {
    const csakRaktaron = document.getElementById('csakRaktaronCheckbox').checked;
    const kartyak = document.querySelectorAll('.tok-kartya');

    kartyak.forEach(kartya => {
        const raktaronVan = kartya.getAttribute('data-raktaron') === "true";

        if (csakRaktaron && !raktaronVan) {
            kartya.style.display = 'none';
        } else {
            kartya.style.display = 'flex';
        }
    });
}

// Ár és név szerinti rendezés szimulációja
function rendezesFunkcio() {
    const rendezesTipus = document.getElementById('rendezesSelect').value;
    const racs = document.getElementById('termekRacs');
    const kartyak = Array.from(racs.querySelectorAll('.tok-kartya'));

    if (rendezesTipus === 'ar-szerint') {
        kartyak.sort((a, b) => parseInt(a.getAttribute('data-ar')) - parseInt(b.getAttribute('data-ar')));
    } else if (rendezesTipus === 'nev-szerint') {
        kartyak.sort((a, b) => {
            const nevA = a.querySelector('.tok-nev').innerText.toLowerCase();
            const nevB = b.querySelector('.tok-nev').innerText.toLowerCase();
            return nevA.localeCompare(nevB);
        });
    }

    // Újraépítjük a rácsot a rendezett elemekkel
    racs.innerHTML = '';
    kartyak.forEach(kartya => racs.appendChild(kartya));
}





// A PHP-válaszból kapott termékekből termékkártyákat készít
function termekekMegjelenitese(termekek) {
    const racs = document.getElementById('termekRacs');

    if (!racs) {
        return;
    }

    // Töröljük a HTML-ben lévő próbakártyákat és az előző találatokat
    racs.replaceChildren();

    // Üres lista esetén visszajelzést adunk a felhasználónak
    if (!Array.isArray(termekek) || termekek.length === 0) {
        const uzenet = document.createElement('p');
        uzenet.className = 'nincs-talalat';
        uzenet.textContent = 'Nincs a keresési feltételeknek megfelelő termék.';
        racs.appendChild(uzenet);
        return;
    }

    termekek.forEach(termek => {
        const kartya = document.createElement('div');
        kartya.className = 'tok-kartya';
        kartya.dataset.ar = termek.ar;
        kartya.dataset.raktaron = Number(termek.keszlet) > 0 ? 'true' : 'false';

        const kep = document.createElement('div');
        kep.className = 'kep-doboz';
        kep.textContent = 'Tok kép';

        const nev = document.createElement('h3');
        nev.className = 'tok-nev';
        nev.textContent =
            `${termek.termek_nev} – ${termek.telefon_modell} (${termek.szin})`;

        const ar = document.createElement('p');
        ar.className = 'tok-ar';
        ar.textContent = `${Number(termek.ar).toLocaleString('hu-HU')} Ft`;

        const keszlet = document.createElement('span');
        keszlet.className = Number(termek.keszlet) > 0
            ? 'keszlet-statusz raktaron'
            : 'keszlet-statusz elfogyott';
        keszlet.textContent = Number(termek.keszlet) > 0
            ? `Raktáron: ${termek.keszlet} db`
            : 'Elfogyott';

        // A kosáremoji csak díszítő elem, nem végez műveletet
        const kosarEmoji = document.createElement('span');
        kosarEmoji.textContent = ' 🛒';
        kosarEmoji.setAttribute('aria-hidden', 'true');
        kartya.append(kep, nev, ar, kosarEmoji, keszlet);
        racs.appendChild(kartya);
    });

    // Az aktuális rendezést és készletszűrést az új kártyákra is alkalmazzuk
    rendezesFunkcio();
    szuresRaktar();
}

// Egy adatbázisból kapott listát betölt egy lenyíló mezőbe
function selectOpcioiBetoltese(selectId, alapErtekSzoveg, ertekek) {
    const select = document.getElementById(selectId);

    if (!select) {
        return;
    }

    const alapOpcio = document.createElement('option');
    alapOpcio.value = '';
    alapOpcio.textContent = alapErtekSzoveg;

    // A lista korábbi elemeit az adatbázisból érkezőkre cseréljük
    select.replaceChildren(alapOpcio);

    ertekek.forEach(ertek => {
        const opcio = document.createElement('option');
        opcio.value = ertek;
        opcio.textContent = ertek;
        select.appendChild(opcio);
    });
}

// Adatbázisból kapott listából jelölőnégyzeteket készít
function checkboxOpcioiBetoltese(tartoId, nev, ertekek) {
    const tarto = document.getElementById(tartoId);

    if (!tarto) {
        return;
    }

    tarto.replaceChildren();

    ertekek.forEach(ertek => {
        const cimke = document.createElement('label');
        cimke.className = 'checkbox-container';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = `${nev}[]`;
        checkbox.value = ertek;

        cimke.append(checkbox, document.createTextNode(` ${ertek}`));
        tarto.appendChild(cimke);
    });
}

// A PHP által visszaküldött adatbázis-opciókat megjeleníti a szűrőkben
function szuroOpcioiMegjelenitese(opciok) {
    selectOpcioiBetoltese('markaSzuro', 'Minden márka', opciok.markak || []);
    selectOpcioiBetoltese('modellSzuro', 'Minden modell', opciok.modellek || []);
    selectOpcioiBetoltese('szinSzuro', 'Minden szín', opciok.szinek || []);

    checkboxOpcioiBetoltese('anyagOpcioLista', 'anyag', opciok.anyagok || []);
    checkboxOpcioiBetoltese('mintaOpcioLista', 'minta', opciok.mintak || []);
}

// A PHP-végpont címe a tokok_Szures.html fájlhoz képest
const apiCim = '../../Backend/adatbazis/get_products.php';

// A keresősáv és az URL-ben kapott keresőkifejezés
let keresesIdozito;
let szuroOpcioiBetoltve = false;

const keresoInput = document.querySelector('.kereso-sav input');
const urlParameterek = new URLSearchParams(window.location.search);
const urlKereses = urlParameterek.get('kereso');

// Ha a főoldalról keresőkifejezéssel érkeztünk, megjelenítjük a mezőben
if (keresoInput && urlKereses !== null) {
    keresoInput.value = urlKereses;
}

// Lekéri az adatbázisból a termékeket az aktuális keresési feltételekkel
async function termekekLekerdezese() {
    try {
        const parameterek = new URLSearchParams();

        const marka = document.getElementById('markaSzuro')?.value || '';
        const modell = document.getElementById('modellSzuro')?.value || '';
        const szin = document.getElementById('szinSzuro')?.value || '';
        const keresesiKifejezes = keresoInput?.value.trim() || '';

        // Összegyűjtjük a bejelölt anyagokat
        const anyagok = Array.from(
            document.querySelectorAll('input[name="anyag[]"]:checked')
        ).map(checkbox => checkbox.value);

        // Összegyűjtjük a bejelölt mintákat
        const mintak = Array.from(
            document.querySelectorAll('input[name="minta[]"]:checked')
        ).map(checkbox => checkbox.value);

        anyagok.forEach(anyag => parameterek.append('anyag[]', anyag));
        mintak.forEach(minta => parameterek.append('minta[]', minta));

        if (marka) {
            parameterek.set('marka', marka);
        }

        if (modell) {
            parameterek.set('modell', modell);
        }

        if (szin) {
            parameterek.set('szin', szin);
        }

        if (keresesiKifejezes) {
            parameterek.set('kereso', keresesiKifejezes);
        }

        const cim = parameterek.toString()
            ? `${apiCim}?${parameterek.toString()}`
            : apiCim;

        const valasz = await fetch(cim);

        if (!valasz.ok) {
            throw new Error(`HTTP hiba: ${valasz.status}`);
        }

        const adat = await valasz.json();

        if (!adat.siker) {
            throw new Error(adat.hiba || 'A termékek lekérése nem sikerült.');
        }

        // Az adatbázisból kapott szűrőlistákat csak az első sikeres válasznál töltjük be
        if (!szuroOpcioiBetoltve && adat.opciok) {
            szuroOpcioiMegjelenitese(adat.opciok);
            szuroOpcioiBetoltve = true;
        }

        termekekMegjelenitese(adat.talalatok);
    } catch (hiba) {
        console.error('Nem sikerült lekérni a termékeket:', hiba);
    }
}

// Gépelés közben 400 ms-ot várunkés csak utána kérjük le újra a találatokat
keresoInput?.addEventListener('input', () => {
    clearTimeout(keresesIdozito);
    keresesIdozito = setTimeout(termekekLekerdezese, 400);
});

// A márka, modell vagy szín megváltoztatásakor újra lekérjük a termékeket
['markaSzuro', 'modellSzuro', 'szinSzuro'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', termekekLekerdezese);
});

// A dinamikusan létrejövő anyag és minta pipálók változását is figyeljük
document.getElementById('anyagOpcioLista')
    ?.addEventListener('change', termekekLekerdezese);

document.getElementById('mintaOpcioLista')
    ?.addEventListener('change', termekekLekerdezese);

// Első betöltéskor is lekérjük a termékeket és a szűrőopciókat
termekekLekerdezese();