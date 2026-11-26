// Az admin felhasználókezelő végpontja
const felhasznaloApi = '../../Backend/adatbazis/admin_users.php';

let felhasznalok = [];
let aktualisAdminId = null;
let aktivModositas = null;

// Üzenetet ír ki az oldalsávban a rendelés vagy jelszógomb alatt
function adminMenuUzenetMegjelenitese(szoveg) {
    const uzenet = document.getElementById('adminMenuUzenet');

    if (uzenet) {
        uzenet.textContent = szoveg;
    }
}

// Üzenetet jelenít meg az oldalon
function adminUzenetMegjelenitese(szoveg, hiba = false) {
    const uzenet = document.getElementById('adminUzenet');

    if (!uzenet) {
        return;
    }

    uzenet.textContent = szoveg;
    uzenet.style.color = hiba ? 'red' : '';
}

// Lekéri az adatbázisból a felhasználókat
async function felhasznalokBetoltese() {
    try {
        const valasz = await fetch(felhasznaloApi, {
            credentials: 'same-origin',
        });

        if (valasz.status === 401) {
            window.location.href = 'admin_Bejelentkezes.html';
            return;
        }

        const adat = await valasz.json();

        if (!valasz.ok || !adat.siker) {
            throw new Error(adat.hiba || 'A felhasználók betöltése nem sikerült.');
        }

        aktualisAdminId = Number(adat.aktualis_admin_id);
        felhasznalok = adat.felhasznalok || [];

        felhasznaloListaMegjelenitese();
    } catch (hiba) {
        console.error('Felhasználóbetöltési hiba:', hiba);
        adminUzenetMegjelenitese(hiba.message, true);
    }
}

// Megjeleníti az adatbázisból kapott felhasználókat
function felhasznaloListaMegjelenitese() {
    const lista = document.getElementById('felhasznaloLista');

    if (!lista) {
        return;
    }

    lista.replaceChildren();

    if (felhasznalok.length === 0) {
        const uzenet = document.createElement('p');
        uzenet.textContent = 'Nincs megjeleníthető felhasználó.';
        lista.appendChild(uzenet);
        return;
    }

    felhasznalok.forEach(felhasznalo => {
        const sor = document.createElement('div');
        sor.className = 'felhasznalo-sor';
        sor.dataset.nev = felhasznalo.nev;
        sor.dataset.email = felhasznalo.email;
        sor.dataset.szerep = felhasznalo.szerepkor;
        sor.dataset.aktiv = String(Number(felhasznalo.aktiv));

        const infok = document.createElement('div');
        infok.className = 'felhasznalo-infok';
        infok.textContent =
            `${felhasznalo.nev} - ${felhasznalo.email} - `
            + `${Number(felhasznalo.aktiv) === 1 ? 'Aktív' : 'Inaktív'}`;

        const muveletek = document.createElement('div');
        muveletek.className = 'felhasznalo-muveletek';

        const szerepSelect = document.createElement('select');
        szerepSelect.className = 'szerep-valaszto';

        [
            { ertek: 'vasarlo', cimke: 'Regisztrált vásárló' },
            { ertek: 'admin', cimke: 'Adminisztrátor' },
        ].forEach(opcioAdat => {
            const opcio = document.createElement('option');
            opcio.value = opcioAdat.ertek;
            opcio.textContent = opcioAdat.cimke;
            szerepSelect.appendChild(opcio);
        });

        szerepSelect.value = felhasznalo.szerepkor;

        const sajatFiok = Number(felhasznalo.felhasznalo_id) === aktualisAdminId;
        szerepSelect.disabled = sajatFiok;
        szerepSelect.addEventListener('change', () => {
            szerepValtoztatasAsztal(felhasznalo, szerepSelect);
        });

        const allapotGomb = document.createElement('button');
        allapotGomb.type = 'button';
        allapotGomb.className = 'mentes-kis-gomb';
        allapotGomb.textContent =
            Number(felhasznalo.aktiv) === 1 ? 'Letiltás' : 'Aktiválás';
        allapotGomb.disabled = sajatFiok;
        allapotGomb.addEventListener('click', () => {
            felhasznaloAllapotValtoztatas(felhasznalo);
        });


        // Ez a gomb továbbra is letiltja vagy aktiválja a fiókot
        const torlesGomb = document.createElement('button');
        torlesGomb.type = 'button';
        torlesGomb.className = 'mentes-kis-gomb';
        torlesGomb.textContent = 'Törlés';
        torlesGomb.disabled = sajatFiok;
        torlesGomb.addEventListener('click', () => {
            felhasznaloTorlese(felhasznalo);
        });

        muveletek.append(szerepSelect, allapotGomb, torlesGomb);
        sor.append(infok, muveletek);
        lista.appendChild(sor);
    });

    felhasznaloSzures();
}

// Szerepkörmódosítás előtt megerősítést kér
function szerepValtoztatasAsztal(felhasznalo, selectElem) {
    if (selectElem.value === felhasznalo.szerepkor) {
        document.getElementById('megerositoPanel').style.display = 'none';
        aktivModositas = null;
        return;
    }

    aktivModositas = { felhasznalo, selectElem };

    document.getElementById('celFelhasznaloNev').textContent = felhasznalo.nev;
    document.getElementById('ujSzerepNev').textContent =
        selectElem.value === 'admin'
            ? 'Adminisztrátor'
            : 'Regisztrált vásárló';
    document.getElementById('megerositoPanel').style.display = 'block';
}

// A megerősített szerepkört elmenti az adatbázisba
async function szerepVeglegesites() {
    if (!aktivModositas) {
        return;
    }

    const { felhasznalo, selectElem } = aktivModositas;
    const eredmeny = await felhasznaloMentes(
        felhasznalo,
        selectElem.value,
        Number(felhasznalo.aktiv)
    );

    if (eredmeny) {
        document.getElementById('megerositoPanel').style.display = 'none';
        aktivModositas = null;
        await felhasznalokBetoltese();
    }
}

// Elveti a szerepkör módosítását
function szerepMegse() {
    if (aktivModositas) {
        aktivModositas.selectElem.value = aktivModositas.felhasznalo.szerepkor;
    }

    document.getElementById('megerositoPanel').style.display = 'none';
    aktivModositas = null;
}

// Aktív és inaktív állapot között vált
async function felhasznaloAllapotValtoztatas(felhasznalo) {
    const ujAktiv = Number(felhasznalo.aktiv) === 1 ? 0 : 1;
    const muveletSzoveg = ujAktiv === 1 ? 'aktiválod' : 'letiltod';

    if (!confirm(`Biztosan ${muveletSzoveg} ${felhasznalo.nev} fiókját?`)) {
        return;
    }

    const eredmeny = await felhasznaloMentes(
        felhasznalo,
        felhasznalo.szerepkor,
        ujAktiv
    );

    if (eredmeny) {
        await felhasznalokBetoltese();
    }
}


// Törli a fiókot, miután a backend archiválta a rendeléseket
async function felhasznaloTorlese(felhasznalo) {
    if (!confirm(
        `Biztosan törlöd ${felhasznalo.nev} fiókját? `
        + 'A rendelései archívumba kerülnek.'
    )) {
        return;
    }

    try {
        const valasz = await fetch(felhasznaloApi, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                felhasznalo_id: Number(felhasznalo.felhasznalo_id),
            }),
        });

        const adat = await valasz.json();

        if (valasz.status === 401) {
            window.location.href = 'admin_Bejelentkezes.html';
            return;
        }

        if (!valasz.ok || !adat.siker) {
            throw new Error(adat.hiba || 'A törlés nem sikerült.');
        }

        adminUzenetMegjelenitese(adat.uzenet);
        await felhasznalokBetoltese();
    } catch (hiba) {
        console.error('Felhasználótörlési hiba:', hiba);
        adminUzenetMegjelenitese(hiba.message, true);
    }
    }

    // Elküldi a módosított felhasználói adatokat a PHP-ból
    async function felhasznaloMentes(felhasznalo, szerepkor, aktiv) {
    try {
        const valasz = await fetch(felhasznaloApi, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                felhasznalo_id: Number(felhasznalo.felhasznalo_id),
                szerepkor,
                aktiv,
            }),
        });

        const adat = await valasz.json();

        if (valasz.status === 401) {
            window.location.href = 'admin_Bejelentkezes.html';
            return false;
        }

        if (!valasz.ok || !adat.siker) {
            throw new Error(adat.hiba || 'A módosítás nem sikerült.');
        }

        adminUzenetMegjelenitese(adat.uzenet);
        return true;
    } catch (hiba) {
        console.error('Felhasználómentési hiba:', hiba);
        adminUzenetMegjelenitese(hiba.message, true);
        return false;
    }
}

// Keresés név és e-mail szerint, szerepkör szerinti szűréssel
function felhasznaloSzures() {
    const keresoErtek = document.getElementById('keresoInput')
        ?.value.trim()
        .toLocaleLowerCase('hu') || '';
    const szerepErtek = document.getElementById('szerepSzuro')?.value || '';

    document.querySelectorAll('#felhasznaloLista .felhasznalo-sor').forEach(sor => {
        const nev = (sor.dataset.nev || '').toLocaleLowerCase('hu');
        const email = (sor.dataset.email || '').toLocaleLowerCase('hu');

        const keresesiTalalat =
            nev.includes(keresoErtek) || email.includes(keresoErtek);
        const szerepTalalat =
            szerepErtek === '' || sor.dataset.szerep === szerepErtek;

        sor.style.display = keresesiTalalat && szerepTalalat ? 'flex' : 'none';
    });
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

// Betöltéskor lekéri a felhasználókat az adatbázisból
document.addEventListener('DOMContentLoaded', felhasznalokBetoltese);