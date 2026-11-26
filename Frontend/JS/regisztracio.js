// Megjeleníti a regisztráció eredményét
function regisztraciosUzenet(szoveg, hiba = false) {
    const doboz = document.getElementById('hibaDoboz');

    doboz.textContent = szoveg;
    doboz.style.display = 'block';
    doboz.style.backgroundColor = hiba ? '#fff5f5' : '#eef8ee';
    doboz.style.borderColor = hiba ? '#ff4d4d' : '#4caf50';
    doboz.style.color = hiba ? '#b42318' : '#256029';
}

// Ellenőrzi az adatokat, majd elküldi a regisztrációt a PHP-végpontnak
async function handleRegistration(event) {
    event.preventDefault();

    const nev = document.getElementById('nev').value.trim();
    const email = document.getElementById('email').value.trim();
    const jelszo = document.getElementById('jelszo').value;
    const jelszoUjra = document.getElementById('jelszoUjra').value;
    const nevMezo = document.getElementById('nev');
    const emailMezo = document.getElementById('email');

    document.getElementById('hibaDoboz').style.display = 'none';

    if (!/\p{L}/u.test(nev)) {
        regisztraciosUzenet('A név legalább egy betűt tartalmazzon.', true);
        nevMezo.focus();
        return;
    }

    if (!emailMezo.checkValidity()) {
        regisztraciosUzenet('Adj meg érvényes e-mail-címet.', true);
        emailMezo.focus();
        return;
    }

    const jelszoHossza = [...jelszo].length;
    const erosJelszo =
        jelszoHossza >= 8
        && /\p{Ll}/u.test(jelszo)
        && /\p{Lu}/u.test(jelszo)
        && /\p{N}/u.test(jelszo)
        && /[\p{P}\p{S}]/u.test(jelszo);

    if (!erosJelszo) {
        regisztraciosUzenet(
            'A jelszó legalább 8 karakter legyen, kis- és nagybetűvel, számmal és különleges karakterrel.',
            true
        );
        document.getElementById('jelszo').focus();
        return;
    }

    if (jelszo !== jelszoUjra) {
        regisztraciosUzenet('A két jelszó nem egyezik.', true);
        document.getElementById('jelszoUjra').focus();
        return;
    }

    try {
        const valasz = await fetch('../../Backend/adatbazis/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ nev, email, jelszo }),
        });

        const adat = await valasz.json();

        if (!valasz.ok || !adat.siker) {
            throw new Error(adat.hiba || 'A regisztráció nem sikerült.');
        }

        alert(adat.uzenet || 'Sikeres regisztráció. Most már bejelentkezhetsz.');
        window.location.href = 'admin_Bejelentkezes.html';
    } catch (hiba) {
        console.error('Regisztrációs hiba:', hiba);
        regisztraciosUzenet(hiba.message || 'A szerver nem érhető el.', true);
    }
}