const keresesiUrlap = document.querySelector('.kereso-sav');
const keresesiLink = document.querySelector('.kiemelt-link');

keresesiLink?.addEventListener('click', (event) => {
    event.preventDefault();
    keresesiUrlap?.requestSubmit();
});

// A főoldali termék és márkakártyák betöltése az adatbázisból
async function fooldaliAdatokBetoltese() {
    const markaKontener = document.querySelector('.kategoria-resz .negy-oszlop');
    const termekKontener = document.querySelector('.termekek-resz .negy-oszlop');

    try {
        // Lekérjük a termékeket és az adatbázisban szereplő márkákat
        const valasz = await fetch('./Backend/adatbazis/get_products.php');

        if (!valasz.ok) {
            throw new Error(`HTTP hiba: ${valasz.status}`);
        }

        const adat = await valasz.json();

        if (!adat.siker) {
            throw new Error(adat.hiba || 'A főoldali adatok betöltése nem sikerült.');
        }

        // A márkakártyákat az adatbázisból kapott márkalistából készítjük
        if (markaKontener) {
            markakMegjelenitese(markaKontener, adat.opciok?.markak || []);
        }

        // A termékváltozatok közül termékenként egyet tartunk meg
        // Az adott termék legolcsóbb változatának árát mutatjuks
        const egyediTermekek = new Map();

        (adat.talalatok || []).forEach(termek => {
            const termekId = String(termek.termek_id);
            const korabbiTermek = egyediTermekek.get(termekId);

            if (!korabbiTermek || Number(termek.ar) < Number(korabbiTermek.ar)) {
                egyediTermekek.set(termekId, termek);
            }
        });

        // A legnagyobb termek_id jelöli a legutóbb felvitt termékeket
        const legujabbTermekek = Array.from(egyediTermekek.values())
            .sort((a, b) => Number(b.termek_id) - Number(a.termek_id))
            .slice(0, 4);

        if (termekKontener) {
            termekekMegjelenitese( termekKontener, legujabbTermekek);
        }
    } catch (hiba) {
        console.error('Nem sikerült betölteni a főoldali adatokat:', hiba);
    }
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

    markak.forEach(marka => {
        const kartya = document.createElement('a');
        kartya.className = 'marka-kartya';

        // A márkanévre kattintva a szűrőoldal a márkára keres
        const celUrl = new URL('./Frontend/HTML/tokok_Szures.html', window.location.href);
        celUrl.searchParams.set('kereso', marka);
        kartya.href = celUrl.href;

        const kepHelye = document.createElement('div');
        kepHelye.className = 'kep-helye';
        kepHelye.textContent = 'Tok kép';

        const cim = document.createElement('h3');
        cim.textContent = marka;

        const leiras = document.createElement('p');
        leiras.textContent = `Telefontokok ${marka} készülékekhez`;

        kartya.append(kepHelye, cim, leiras);
        kontener.appendChild(kartya);
    });
}

// Adatbázisból kapott termékekből készít főoldali kártyákat
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
        kepHelye.textContent = 'Tok kép';

        const cim = document.createElement('h3');
        cim.textContent = termek.termek_nev;

        const ar = document.createElement('p');
        ar.className = 'ar';
        ar.textContent = `${Number(termek.ar).toLocaleString('hu-HU')} Ft-tól`;

        // A kosáremoji csak díszítő elem, nem végez műveletet
        const kosarEmoji = document.createElement('span');
        kosarEmoji.textContent = ' 🛒';
        kosarEmoji.setAttribute('aria-hidden', 'true');

        ar.appendChild(kosarEmoji);
        kartya.append(kepHelye, cim, ar);
        kontener.appendChild(kartya);
    });
}

// Betöltjük a főoldali adatokat amikor elkészült
document.addEventListener('DOMContentLoaded', fooldaliAdatokBetoltese);