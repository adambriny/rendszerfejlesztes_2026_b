// Termék betöltése szerkesztésre az űrlapba
function termekSzerkesztes(nev, modell, ar, keszlet, anyag) {
    document.getElementById('formCim').innerText = "Termék szerkesztése";
    document.getElementById('termekNev').value = nev;
    document.getElementById('termekModell').value = modell;
    document.getElementById('termekAr').value = ar;
    document.getElementById('termekKeszlet').value = keszlet;
    document.getElementById('termekAnyag').value = anyag;
    
    document.getElementById('formSzekcio').scrollIntoView({ behavior: 'smooth' });
}

// Űrlap átváltása új termék módba
function ujTermekLetrehozasa() {
    document.getElementById('formCim').innerText = "Új termék hozzáadása";
    formUrites();
    document.getElementById('formSzekcio').scrollIntoView({ behavior: 'smooth' });
}

// Mentés gomb lekezelése
function termekMentese(event) {
    event.preventDefault();
    const nev = document.getElementById('termekNev').value;
    alert('A(z) "' + nev + '" termék adatai sikeresen elmentve!');
}

// Sor törlése a listából
function termekTorles(gomb) {
    if (confirm('Biztosan törölni szeretnéd ezt a terméket?')) {
        const sor = gomb.closest('.termek-sor');
        sor.remove();
    }
}

// Form mezők ürítése
function formUrites() {
    document.getElementById('termekForm').reset();
}

// Élő szűrés név és márka alapján
function szuresFunkcio() {
    const keresoErtek = document.getElementById('keresoInput').value.toLowerCase();
    const markaErtek = document.getElementById('markaSzuro').value;
    const sorok = document.querySelectorAll('.termek-sor');

    sorok.forEach(sor => {
        const termekNev = sor.getAttribute('data-nev').toLowerCase();
        const termekMarka = sor.getAttribute('data-marka');

        const nevEgyezik = termekNev.includes(keresoErtek);
        const markaEgyezik = (markaErtek === "" || termekMarka === markaErtek);

        if (nevEgyezik && markaEgyezik) {
            sor.style.display = 'flex';
        } else {
            sor.style.display = 'none';
        }
    });
}

// Kijelentkezéskezelő
function kijelentkezes() {
    if (confirm('Biztosan ki szeretnél jelentkezni az Admin felületről?')) {
        window.location.href = '../../index.html';
    }
}
