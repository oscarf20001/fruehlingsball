document.getElementById('main').addEventListener('submit', function(e) {
    e.preventDefault(); // Verhindert das Standard-Formular-Absendeverhalten

    document.getElementById('responseMessage').innerHTML = "Eine Sekunde... Gordon ackert und bearbeitet deine Bestellung!";
    
    // Formulardaten abrufen
    const formData = new FormData(this);

    // AJAX-Anfrage erstellen
    fetch('makeReservation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Netzwerkantwort war nicht ok ' + response.statusText);
        }
        return response.json(); // JSON-Antwort parsen
    })
    .then(data => {
        if (Array.isArray(data)) {
            // Alle Nachrichten ausgeben
            data.forEach(item => {
                if(item.status == 'valid'){
                    //document.getElementById('responseMessage').innerHTML = item.message;
                    console.log(item.message);
                }else if(item.status == 'success'){
                    document.getElementById('responseMessage').innerHTML = item.message;
                    document.getElementById('responseMessage').style.color = 'green';
                    alert(item.message);
                }else{
                    //console.log(item.message);
                    document.getElementById('responseMessage').innerHTML = item.message;
                    document.getElementById('responseMessage').style.color = 'red';
                    alert(item.message);
                }
            });
        } else {
            console.log('Die Antwort ist nicht wie erwartet.');
        }
    })
    .catch(error => {
        console.error('Es gab ein Problem mit der Fetch-Operation:', error);
    });
});