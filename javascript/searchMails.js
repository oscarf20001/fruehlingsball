function searchMails() {
    const input = document.getElementById('f-email').value;

    if (input.length === 0) {
        document.getElementById('suggestions').innerHTML = '';
        return; // Leerer Input löscht Vorschläge
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'searchEmails.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('suggestions').innerHTML = xhr.responseText;
        }
    };
    xhr.send('query=' + encodeURIComponent(input));
}

function selectMail(email) {
    document.getElementById('f-email').value = email;
    document.getElementById('suggestions').innerHTML = ''; // Vorschläge ausblenden
}
