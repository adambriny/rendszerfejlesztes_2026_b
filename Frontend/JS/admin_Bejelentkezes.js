// Ellenőrzi az admin belépési adatokat a PHP-n
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

        // Sikeres belépés után megnyitjuk a termékkezelő oldalt
        window.location.href = 'admin_Termekek_Kezelese.html';
    } catch (hiba) {
        console.error('Belépési hiba:', hiba);
        hibaDoboz.textContent = 'A szerver nem érhető el.';
        hibaDoboz.style.display = 'block';
    }
}