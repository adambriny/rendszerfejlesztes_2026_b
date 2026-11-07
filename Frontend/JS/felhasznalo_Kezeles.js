let aktivModositas = null;

function szerepValtoztatasAsztal(felhasznaloNev, selectElem) {
    const ujSzerep = selectElem.value;
    const eredetiSzerep = selectElem.closest('.felhasznalo-sor').getAttribute('data-szerep');

    if (ujSzerep === eredetiSzerep) {
        document.getElementById('megerositoPanel').style.display = 'none';
        aktivModositas = null;
        return;
    }

    aktivModositas = {
        felhasznaloNev: felhasznaloNev,
        ujSzerep: ujSzerep,
        selectElem: selectElem
    };

    document.getElementById('celFelhasznaloNev').innerText = felhasznaloNev;
    document.getElementById('ujSzerepNev').innerText = ujSzerep;

    document.getElementById('megerositoPanel').style.display = 'block';
}

function szerepVeglegesites() {
    if (aktivModositas) {
        const sor = aktivModositas.selectElem.closest('.felhasznalo-sor');
        
        sor.setAttribute('data-szerep', aktivModositas.ujSzerep);
        
        alert('Sikeresen módosítva! Új szerepkör: ' + aktivModositas.ujSzerep);
        
        document.getElementById('megerositoPanel').style.display = 'none';
        aktivModositas = null;
    }
}

function szerepMegse() {
    if (aktivModositas) {
        const eredetiSzerep = aktivModositas.selectElem.closest('.felhasznalo-sor').getAttribute('data-szerep');
        
        aktivModositas.selectElem.value = eredetiSzerep;
        
        document.getElementById('megerositoPanel').style.display = 'none';
        aktivModositas = null;
    }
}

function mentesErtesites(felhasznaloNev) {
    alert(felhasznaloNev + ' felhasználó adatai frissítve lettek a rendszerben.');
}

function felhasznaloSzures() {
    const keresoErtek = document.getElementById('keresoInput').value.toLowerCase();
    const szerepErtek = document.getElementById('szerepSzuro').value;
    const sorok = document.querySelectorAll('.felhasznalo-sor');

    sorok.forEach(sor => {
        const nev = sor.getAttribute('data-nev').toLowerCase();
        const email = sor.getAttribute('data-email').toLowerCase();
        const szerep = sor.getAttribute('data-szerep');

        const keresesEgyezik = nev.includes(keresoErtek) || email.includes(keresoErtek);
        const szerepEgyezik = (szerepErtek === "" || szerep === szerepErtek);

        if (keresesEgyezik && szerepEgyezik) {
            sor.style.display = 'flex';
        } else {
            sor.style.display = 'none';
        }
    });
}

function kijelentkezes() {
    if (confirm('Biztosan ki szeretnél jelentkezni az Admin felületről?')) {
        window.location.href = '../../index.html';
    }
}
