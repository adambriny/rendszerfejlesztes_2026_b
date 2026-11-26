// Bekéri az e-mail-címet, majd megjeleníti az új jelszót
document
    .getElementById('elfelejtettJelszoLink')
    ?.addEventListener('click', async event => {
        event.preventDefault();

        const email = prompt('Add meg a fiókodhoz tartozó e-mail-címet:')?.trim();

        if (!email) {
            return;
        }

        try {
            const valasz = await fetch('../../Backend/adatbazis/reset_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email }),
            });

            const adat = await valasz.json();

            if (!valasz.ok || !adat.siker) {
                alert(adat.hiba || 'Az új jelszó létrehozása nem sikerült.');
                return;
            }

            alert(`Az új jelszavad: ${adat.uj_jelszo}`);
        } catch (hiba) {
            console.error('Jelszó-visszaállítási hiba:', hiba);
            alert('A szerver nem érhető el.');
        }
    });

// Ellenőrzi a belépési adatokat a PHP-n
async function handleLogin(event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const jelszo = document.getElementById('jelszo').value;
    const hibaDoboz = document.getElementById('hibaDoboz');

    hibaDoboz.style.display = 'none';

    try {
        const valasz = await fetch('../../Backend/adatbazis/admin_login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, jelszo }),
        });

        const adat = await valasz.json();

        if (!valasz.ok || !adat.siker) {
            hibaDoboz.textContent = adat.hiba || 'A belépés nem sikerült.';
            hibaDoboz.style.display = 'block';
            return;
        }

        // Az admin az adminfelületre, a vásárló a webshop főoldalára kerül
        if (adat.szerepkor === 'admin') {
            window.location.href = 'admin_Termekek_Kezelese.html';
        } else {
            window.location.href = '../../index.html';
        }
    } catch (hiba) {
        console.error('Belépési hiba:', hiba);
        hibaDoboz.textContent = 'A szerver nem érhető el.';
        hibaDoboz.style.display = 'block';
    }
}