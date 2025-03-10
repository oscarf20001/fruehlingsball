document.getElementById('main').addEventListener('submit', function(e) {
    e.preventDefault(); // Verhindert das Standard-Formular-Absendeverhalten

    showMessage("gordon");
    
    // Formulardaten abrufen
    const formData = new FormData(this);

    // AJAX-Anfrage erstellen
    fetch('/../ticket/makeReservation.php', {
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
                    showMessage('success',item.message);
                }else{
                    console.log(item.message);
                    showMessage('duplicate',item.message);
                }
            });
        } else {
            showMessage('unexpected');
            console.log('Die Antwort ist nicht wie erwartet.');
        }
    })
    .catch(error => {
        showMessage('fetch');
        console.error('Es gab ein Problem mit der Fetch-Operation:', error);
    });
});

let txt = null;
let returnWert = null;

function showMessage(msg, returnWert = null){
    let support = 'Bitte wende dich an: streiosc@curiegym.de';

    switch (msg) {
        case 'gordon':
            txt = "... Eine Sekunde... Gordon ackert und bearbeitet deine Bestellung!";
            break;

        case 'success':
            txt = '✅ ' + returnWert;
            break;

        case 'fail':
            txt = '❌ ' + returnWert;
            break;

        case 'duplicate':
            txt = '❌ Anfrage verweigert! Dieses Ticket existiert bereits. Bezahle dein Ticket oder wende dich mit "Duplicate Entry" an: ' + support;
            break;

        case 'unexpected':
            txt = '❌ Die Antwort des Servers ist nicht wie erwartet. ' + support;
            break;
        
        case 'fetch':
            txt = '❌ Es gab ein Problem mit der Fetch-Operation: ' + support;
            break;

        default:
            txt = '❌ Ein unbekannter Fehler ist aufgetreten. ' + support;
            break;
    }

    document.getElementById('responseText').innerText = txt;
    document.getElementById('responseContainer').style.display = 'flex';
    document.getElementById('removeResponseContainerButton').addEventListener('click', () => {
        document.getElementById('responseContainer').style.display = 'none';
    });
}