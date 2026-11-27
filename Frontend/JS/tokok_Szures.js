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
        kartyak.sort((a, b) =>
            parseInt(a.getAttribute('data-ar')) - parseInt(b.getAttribute('data-ar'))
        );
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

    // Töröljük az előző termékeket
    racs.replaceChildren();

    // Üres lista esetén visszajelzést adunk
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

        if (termek.kep_url && termek.kep_url !== 'placeholder.jpg') {
            const kepElem = document.createElement('img');
            kepElem.src = termek.kep_url;
            kepElem.alt = `${termek.termek_nev} – ${termek.telefon_modell} (${termek.szin})`;
            kepElem.onerror = () => {
                kep.textContent = 'A kép nem tölthető be';
            };
            kep.appendChild(kepElem);
        } else {
            kep.textContent = 'Tok kép';
        }

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

        // A kosáremoji csak díszítő elem
        const kosarEmoji = document.createElement('span');
        kosarEmoji.textContent = ' 🛒';
        kosarEmoji.setAttribute('aria-hidden', 'true');

        kartya.append(kep, nev, ar, kosarEmoji, keszlet);
        racs.appendChild(kartya);
    });

    // Alkalmazzuk az aktuális rendezést és készletszűrést
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

    // Betöltjük az adatbázisból kapott értékeket
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
let aktualisOldal = 1;
let oldalakSzama = 1;

const keresoInput = document.querySelector('.kereso-sav input');
const keresesiUrlap = document.querySelector('.kereso-sav');
const urlParameterek = new URLSearchParams(window.location.search);
const urlKereses = urlParameterek.get('kereso');

// Ha a főoldalról keresőkifejezéssel érkeztünk, megjelenítjük a mezőben
if (keresoInput && urlKereses !== null) {
    keresoInput.value = urlKereses;
}

// A kiválasztott szűrők és keresés alapján frissíti az oldal címét
function oldalcimFrissitese() {
    const marka = document.getElementById('markaSzuro')?.value || '';
    const modell = document.getElementById('modellSzuro')?.value || '';
    const kereses = keresoInput?.value.trim() || '';

    let cim = 'Telefontokok';

    if (modell) {
        cim = `${marka ? `${marka} ` : ''}${modell} tokok`;
    } else if (marka) {
        cim = `${marka} tokok`;
    } else if (kereses) {
        cim = `Keresési találatok: ${kereses}`;
    }

    document.getElementById('termekekCime').textContent = cim;
    document.getElementById('kenyermorzsaAktualis').textContent = cim;
    document.title = `${cim} – CASEFLOW`;
}

// Összegyűjti a keresési feltételeket
function keresesiParameterekOsszegyujtese() {
    const parameterek = new URLSearchParams();
    parameterek.set('oldal', String(aktualisOldal));
    const marka = document.getElementById('markaSzuro')?.value || '';
    const modell = document.getElementById('modellSzuro')?.value || '';
    const szin = document.getElementById('szinSzuro')?.value || '';
    const keresesiKifejezes = keresoInput?.value.trim() || '';

    const anyagok = Array.from(
        document.querySelectorAll('input[name="anyag[]"]:checked')
    ).map(checkbox => checkbox.value);

    const mintak = Array.from(
        document.querySelectorAll('input[name="minta[]"]:checked')
    ).map(checkbox => checkbox.value);

    anyagok.forEach(anyag => parameterek.append('anyag[]', anyag));
    mintak.forEach(minta => parameterek.append('minta[]', minta));

    if (marka) parameterek.set('marka', marka);
    if (modell) parameterek.set('modell', modell);
    if (szin) parameterek.set('szin', szin);
    if (keresesiKifejezes) {
        parameterek.set('kereso', keresesiKifejezes);
    }

    return parameterek;
}

// Megjeleníti az előző és következő oldalt
function lapozoMegjelenitese() {
    const lapozo = document.getElementById('termekLapozas');

    if (!lapozo) {
        return;
    }

    lapozo.replaceChildren();

    if (oldalakSzama <= 1) {
        return;
    }

    const elozoGomb = document.createElement('button');
    elozoGomb.type = 'button';
    elozoGomb.textContent = 'Előző';
    elozoGomb.disabled = aktualisOldal <= 1;
    elozoGomb.addEventListener('click', () => {
        aktualisOldal--;
        termekekLekerdezese();
    });

    const oldalSzam = document.createElement('span');
    oldalSzam.textContent = `${aktualisOldal} / ${oldalakSzama}`;

    const kovetkezoGomb = document.createElement('button');
    kovetkezoGomb.type = 'button';
    kovetkezoGomb.textContent = 'Következő';
    kovetkezoGomb.disabled = aktualisOldal >= oldalakSzama;
    kovetkezoGomb.addEventListener('click', () => {
        aktualisOldal++;
        termekekLekerdezese();
    });

    lapozo.append(elozoGomb, oldalSzam, kovetkezoGomb);
}

// Lekéri az adatbázisból a termékeket POST-kéréssel
async function termekekLekerdezese() {
    oldalcimFrissitese();

    try {
        const valasz = await fetch(apiCim, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: keresesiParameterekOsszegyujtese().toString()
        });

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

        aktualisOldal = Number(adat.aktualis_oldal) || 1;
        oldalakSzama = Number(adat.oldalak_szama) || 1;

        termekekMegjelenitese(adat.talalatok);
        lapozoMegjelenitese();
    } catch (hiba) {
        console.error('Nem sikerült lekérni a termékeket:', hiba);
    }
}

// Törli az oldalsó szűrők beállításait
function oldalszurokTorlese() {
    ['markaSzuro', 'modellSzuro', 'szinSzuro'].forEach(id => {
        const mezo = document.getElementById(id);
        if (mezo) {
            mezo.value = '';
        }
    });

    document
        .querySelectorAll('input[name="anyag[]"], input[name="minta[]"]')
        .forEach(pipa => {
            pipa.checked = false;
        });
}

// Gépeléskor újra lekéri a termékeket
function szovegesKeresesInditasa() {
    oldalszurokTorlese();
    aktualisOldal = 1;
    termekekLekerdezese();
}

// A márka, modell, szín, anyag vagy minta változásakor újra lekéri a termékeket
function oldalszuroModosult() {
    clearTimeout(keresesIdozito);

    if (keresoInput) {
        keresoInput.value = '';
    }
    if (keresoInput) {
        keresoInput.value = '';
    }

    aktualisOldal = 1;
    termekekLekerdezese();
}

// Gépelés közben 400 ms-ot várunk, majd lekérjük a termékeket
keresoInput?.addEventListener('input', () => {
    clearTimeout(keresesIdozito);
    keresesIdozito = setTimeout(szovegesKeresesInditasa, 400);
});

// A márka, modell vagy szín megváltozásakor újra lekérjük a termékeket
['markaSzuro', 'modellSzuro', 'szinSzuro'].forEach(id => {
    document.getElementById(id)
        ?.addEventListener('change', oldalszuroModosult);
});

// Az anyag és minta jelölők változásakor újra lekérjük a termékeket
document.getElementById('anyagOpcioLista')
    ?.addEventListener('change', oldalszuroModosult);

document.getElementById('mintaOpcioLista')
    ?.addEventListener('change', oldalszuroModosult);

// A kereső űrlap beküldésekor nem töltjük újra az oldalt
keresesiUrlap?.addEventListener('submit', event => {
    event.preventDefault();
    clearTimeout(keresesIdozito);
    szovegesKeresesInditasa();
});

// Első betöltéskor is lekérjük a termékeket és a szűrőopciókat
termekekLekerdezese();