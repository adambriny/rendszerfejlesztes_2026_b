// Az admin saját fiókjának PHP-végpontja
const adminFiokApi = '../../Backend/adatbazis/admin_fiok.php';

// Üzenetet jelenít meg a fiók űrlapján
function fiokUzenetMegjelenitese(szoveg, hiba = false) {
    const uzenet = document.getElementById('fiokUzenet');

    if (!uzenet) {
        return;
    }

    uzenet.textContent = szoveg;
    uzenet.className = hiba ? 'fiok-uzenet hiba' : 'fiok-uzenet';
}

// Betölti az adatbázisban tárolt adminadatokat
async function fiokAdatokBetoltese() {
    try {
        const valasz = await fetch(adminFiokApi, {
            credentials: 'same-origin',
        });

        const adat = await valasz.json();

        if (valasz.status === 401) {
            window.location.href = 'admin_Bejelentkezes.html';
            return;
        }

        if (!valasz.ok || !adat.siker) {
            throw new Error(adat.hiba || 'A fiókadatok betöltése nem sikerült.');
        }

        document.getElementById('nev').value = adat.fiok.nev;
        document.getElementById('email').value = adat.fiok.email;
        document.getElementById('szerep').value =
            adat.fiok.szerepkor === 'admin' ? 'Adminisztrátor' : adat.fiok.szerepkor;

        fiokUzenetMegjelenitese('');
    } catch (hiba) {
        console.error('Admin fiók betöltési hiba:', hiba);
        fiokUzenetMegjelenitese(hiba.message, true);
    }
}

// Elmenti a módosított nevet és e-mail-címet
async function adatokMentese(event) {
    event.preventDefault();

    const adatok = {
        nev: document.getElementById('nev').value.trim(),
        email: document.getElementById('email').value.trim(),
    };

    try {
        const valasz = await fetch(adminFiokApi, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(adatok),
        });

        const eredmeny = await valasz.json();

        if (valasz.status === 401) {
            window.location.href = 'admin_Bejelentkezes.html';
            return;
        }

        if (!valasz.ok || !eredmeny.siker) {
            throw new Error(eredmeny.hiba || 'A mentés nem sikerült.');
        }

        document.getElementById('nev').value = eredmeny.fiok.nev;
        document.getElementById('email').value = eredmeny.fiok.email;

        fiokUzenetMegjelenitese(eredmeny.uzenet);
    } catch (hiba) {
        console.error('Admin fiók mentési hiba:', hiba);
        fiokUzenetMegjelenitese(hiba.message, true);
    }
}

// Visszatölti az űrlapra az adatbázisban lévő adatokat
function formAlaphelyzet() {
    if (confirm('Visszaállítod az adatbázisban jelenleg tárolt adatokat?')) {
        fiokAdatokBetoltese();
    }
}

// Az oldalsávban jelzi, ha egy még el nem készült funkcióra kattintanak
function adminMenuUzenetMegjelenitese(szoveg) {
    const uzenet = document.getElementById('adminMenuUzenet');

    if (uzenet) {
        uzenet.textContent = szoveg;
    }
}

// Kijelentkezteti az admint
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

// Az oldal betöltésekor lekérjük a fiókadatokat
document.addEventListener('DOMContentLoaded', fiokAdatokBetoltese);