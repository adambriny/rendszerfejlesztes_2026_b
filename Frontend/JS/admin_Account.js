function adatokMentese(event) {
    event.preventDefault();
    const nev = document.getElementById('nev').value;
    alert('Sikeres mentés! Új név: ' + nev);
}

function formAlaphelyzet() {
    if (confirm('Biztosan visszaállítod az eredeti adatokat?')) {
        document.getElementById('adminForm').reset();
    }
}

