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
