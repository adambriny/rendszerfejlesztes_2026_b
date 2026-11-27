const keresesiUrlap = document.querySelector('.kereso-sav');
const keresesiLink = document.querySelector('.kiemelt-link');

const hirlevelUrlap = document.querySelector('.hirlevel-urlap');
hirlevelUrlap?.addEventListener('submit', (event) => {
    event.preventDefault();
    hirlevelUrlap.reset();
});

keresesiLink?.addEventListener('click', (event) => {
    event.preventDefault();
    keresesiUrlap?.requestSubmit();
});

// A főoldali termék és márkakártyák betöltése az adatbázisból
async function fooldaliAdatokBetoltese() {
    const markaKontener = document.querySelector('.kategoria-resz .negy-oszlop');
    const termekKontener = document.querySelector('.termekek-resz .negy-oszlop');

    try {
        const valasz = await fetch('./Backend/adatbazis/get_homepage.php');

        if (!valasz.ok) {
            throw new Error('HTTP hiba: ' + valasz.status);
        }

        const adat = await valasz.json();

        if (!adat.siker) {
            throw new Error(adat.hiba || 'A főoldali adatok betöltése nem sikerült.');
        }

        if (markaKontener) {
            markakMegjelenitese(markaKontener, adat.markak || []);
        }

        if (termekKontener) {
            termekekMegjelenitese(termekKontener, adat.legujabb_termekek || []);
        }
    } catch (hiba) {
        console.error('Nem sikerült betölteni a főoldali adatokat:', hiba);
    }
}

// Az adatbázisban tárolt url a főoldal helyéhez igazítja
function fooldaliKepUrl(kepUrl) {
    if (!kepUrl || kepUrl === 'placeholder.jpg') {
        return '';
    }

    const fajlNev = String(kepUrl).replace(/\\/g, '/').split('/').pop();
    return './kepek/' + encodeURIComponent(fajlNev);
}

// Képet jelenít meg vagy helykitöltőt használ ha nincs elérhető kép
function kepMegjelenitese(kepHelye, kepUrl, altSzoveg) {
    const fooldaliUrl = fooldaliKepUrl(kepUrl);

    if (!fooldaliUrl) {
        kepHelye.textContent = 'Tok kép';
        return;
    }

    const kep = document.createElement('img');
    kep.src = fooldaliUrl;
    kep.alt = altSzoveg;
    kep.onerror = () => {
        kepHelye.textContent = 'Tok kép';
    };
    kepHelye.appendChild(kep);
}

// Adatbázisból kapott márkákból készít kártyákat
function markakMegjelenitese(kontener, markak) {
    kontener.replaceChildren();

    if (markak.length === 0) {
        const uzenet = document.createElement('p');
        uzenet.textContent = 'Jelenleg nincs megjeleníthető márka.';
        kontener.appendChild(uzenet);
        return;
    }

    markak.forEach(markaAdat => {
        const marka = markaAdat.marka;
        const kartya = document.createElement('a');
        kartya.className = 'marka-kartya';

        // A márkanévre kattintva a szűrőoldal a márkára keres
        const celUrl = new URL('./Frontend/HTML/tokok_Szures.html', window.location.href);
        celUrl.searchParams.set('kereso', marka);
        kartya.href = celUrl.href;

        const kepHelye = document.createElement('div');
        kepHelye.className = 'kep-helye';
        kepMegjelenitese(kepHelye, markaAdat.kep_url, marka + ' legújabb termékképe');

        const cim = document.createElement('h3');
        cim.textContent = marka;

        const leiras = document.createElement('p');
        leiras.textContent = 'Telefontokok ' + marka + ' készülékekhez';

        kartya.append(kepHelye, cim, leiras);
        kontener.appendChild(kartya);
    });
}

// Az adatbázis legújabb négy termékéből készít főoldali kártyákat
function termekekMegjelenitese(kontener, termekek) {
    kontener.replaceChildren();

    if (termekek.length === 0) {
        const uzenet = document.createElement('p');
        uzenet.textContent = 'Jelenleg nincs megjeleníthető termék.';
        kontener.appendChild(uzenet);
        return;
    }

    termekek.forEach(termek => {
        const kartya = document.createElement('div');
        kartya.className = 'termek-kartya';

        const kepHelye = document.createElement('div');
        kepHelye.className = 'kep-helye';
        kepMegjelenitese(kepHelye, termek.kep_url, termek.termek_nev);

        const cim = document.createElement('h3');
        cim.textContent = termek.termek_nev;

        const ar = document.createElement('p');
        ar.className = 'ar';
        ar.textContent = Number(termek.ar).toLocaleString('hu-HU') + ' Ft-tól';

        const kosarEmoji = document.createElement('span');
        kosarEmoji.textContent = ' 🛒';
        kosarEmoji.setAttribute('aria-hidden', 'true');

        ar.appendChild(kosarEmoji);
        kartya.append(kepHelye, cim, ar);
        kontener.appendChild(kartya);
    });
}

document.addEventListener('DOMContentLoaded', fooldaliAdatokBetoltese);