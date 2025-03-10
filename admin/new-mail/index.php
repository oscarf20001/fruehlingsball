<!-- ADMIN PANEL -->
<?php
include '../../affiliations/php/db_connection.php';

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.html");
    exit;
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retry | Resend Mails</title>
    <link rel="stylesheet" href="../../styles/resend.css">
</head>
<body>
    <h1>Mails erneut senden!</h1>
    <p>Hier gibts die Möglichkeit, Mails erneut zu versenden. Wenn eine Person eine Mail nicht bekommen hat, kann man diese hier erneut senden. Dafür die Mail in das Feld unten eintragen und wählen, welche Mail erneut gesendet werden soll!</p>
    <input type="email" name="email" id="email">
    <select name="type" id="typeOfMail">
        <option value="none" disabled selected>-- Bitte auswählen --</option>
        <option value="registration">Reservierungsmail</option>
        <option value="submitation">Bestätigungsmail</option>
        <option value="pay">Zahlungsaufforderung</option>
        <option value="ticket">Ticket</option>
    </select><br>
    <input type="submit" value="Mail versenden!" id="submit">
    <script>

        // ------------------------------------------------------------------------------
        // |                                                                            |
        // |                        1. Search for Mail in DB                            |
        // |                                                                            |
        // ------------------------------------------------------------------------------

        let code = 0;
        let s_submit = document.getElementById('submit');

        s_submit.addEventListener('click', function(){
            let email = document.getElementById('email').value.trim();
            let emailInputField = document.getElementById('email');
            emailInputField.value = '';

            // Email must contain some type of string
            if(!email){
                code = 'EoI_ef';
                console.log("Handing over to error handling function with code: " + code);
                showMessageOnScreen(code);
                return
            }

            // Look up for the email
            fetch('../../affiliations/php/findEmail.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'  // Setze den Content-Type auf JSON
                },
                body: JSON.stringify({ email: email })  // Wandelt die Email in ein JSON-Objekt um
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Netzwerkantwort war nicht ok ' + response.statusText);
                    code = 'N_E'
                    showMessageOnScreen(code);
                }
                return response.json(); // JSON-Antwort parsen
            })
            .then(data =>{
                if(data.status !== 'success'){
                    code = 'Fe_Enf'
                    showMessageOnScreen(code);
                    return;
                }

                // ------------------------------------------------------------------------------
                // |                                                                            |
                // |                        2. Wich Method should we use?                       |
                // |                                                                            |
                // ------------------------------------------------------------------------------

                let method = document.getElementById('typeOfMail').value;
                furtherChecks(email, method);
            })
            .catch(error => {
                code = 'e_uknwn';
                showMessageOnScreen(code);
                console.error('Es gab ein Problem mit der Fetch-Operation:', error);
            })
        });

        async function furtherChecks(email, method){
            let output = null;
            switch (method){
                case 'registration':
                    //action
                    console.log('We have to send the first Email / Registration');

                    // Funktion für Aufruf der Maildatei
                    output = await requestSending(method, email); // Warten auf das Ergebnis
                    console.log('Output: ' + output);
                    break;

                case 'submitation':
                    //action
                    console.log('We have to send the second Email / Kosten bzw. Bestätigung');

                    output = await requestSending(method, email); // Warten auf das Ergebnis
                    console.log('Output: ' + output);
                    break;

                case 'pay':
                    console.log('Zahlungsaufforderung wird gesendet');

                    output = await requestSending(method, email); // Warten auf das Ergebnis
                    console.log('Output: ' + output);
                    break;

                case 'ticket':
                    //action
                    console.log('We have to send third Email / QR-Code bzw. Infos');

                    output = await requestSending(method, email); // Warten auf das Ergebnis
                    console.log('Output: ' + output);
                    break;

                case 'none':
                    //action
                    console.log('Nothing is going to be sended');
                    break;

                default:
                    //action
                    console.log('If we are here, this is not good!')
                    break;
            }

            if(output[0] == "fail"){
                showMessageOnScreen(output[1])
            }
        }

        // ------------------------------------------------------------------------------
        // |                                                                            |
        // |                        3. Send the email                                   |
        // |                                                                            |
        // ------------------------------------------------------------------------------

        async function requestSending(method, email) {
            try {
                const response = await fetch('requestMail.php',{
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ method: method, mail: email})
                });

                if (!response.ok) {
                    throw new Error('Netzwerkantwort war nicht ok ' + response.statusText);
                }
                
                const data = await response.json();
                if (data && data.status && data.email) {
                    console.log('Status:', data.status);
                    console.log('Email:', data.email);
                    console.log('Reason:', data.reason);

                    // Beispiel: Du kannst auch die Nachricht zurückgeben
                    return [data.status, data.reason, data.email];
                } else {
                    throw new Error('Unerwartetes Antwortformat: ' + JSON.stringify(data));
                }
            } catch (error) {
                console.error('Es gab ein Problem mit der Fetch-Operation:', error);
                return null; // Oder du kannst einen Standardwert zurückgeben, falls ein Fehler auftritt
            }
        }

        // ------------------------------------------------------------------------------
        // |                                                                            |
        // |                        4. Show possible Erros on Screen                    |
        // |                                                                            |
        // ------------------------------------------------------------------------------


        function showMessageOnScreen(code){
            switch (code) {
                case 'EoI_ef':
                    msg = 'Empty or Invalid Email field!';
                    console.error('Empty or Invalid Email field!');
                    break;
            
                case 'N_E':
                    msg = 'Network Error!';
                    console.error('Network Error!');
                    break;

                case 'N_E_req':
                    msg = 'Request Error!';
                    console.error('Request Error!');
                    break;
            
                case 'e_uknwn':
                    msg = 'Unknown/Unexpected Error'
                    console.error('Unknown/Unexpected Error');
                    break;

                case 'Fe_Enf':
                    msg = 'Fatal Error. Email wurde nicht in der Datebank gefunden!'
                    console.error('Fatal Error. Email wurde nicht in der Datebank gefunden!');
                    break;
                
                case 'notPayed':
                    msg = 'Email wird nicht gesendet: Person hat noch nicht bezahlt!';
                    console.error('Email wird nicht gesendet: Person hat noch nicht bezahlt!');
                    createFullscreenBlur(msg);
                    break;

                default:
                    msg = 'Unknown/Unexpected Error'
                    console.error('Unknown/Unexpected Error');
                    break;
            }
        }

        function createFullscreenBlur(msg, email) {
            // Erstelle das Div-Element
            const div = document.createElement('div');
            div.style.position = 'fixed';  // Positioniert das Div fest auf dem Bildschirm
            div.style.top = '0';
            div.style.left = '0';
            div.style.width = '100vw';  // Vollbildbreite
            div.style.height = '100vh'; // Vollbildhöhe
            div.style.backgroundColor = 'rgba(0, 0, 0, 0.5)'; // Hintergrund mit halbtransparenter Farbe
            div.style.display = 'flex';
            div.style.justifyContent = 'center'; // Zentrieren des Inhalts horizontal
            div.style.alignItems = 'center'; // Zentrieren des Inhalts vertikal
            div.style.flexDirection = 'column'; // Zentrieren des Inhalts vertikal
            div.style.backdropFilter = 'blur(10px)'; // Wendet den Unschärfeeffekt auf den Hintergrund an
            div.style.zIndex = '1000'; // Stellt sicher, dass es oben auf allen anderen Elementen angezeigt wird

            // Erstelle den Text
            const text = document.createElement('p');
            text.textContent = '❌ ' + msg;
            text.style.color = 'white';
            text.style.fontSize = '24px';
            text.style.fontFamily = 'Arial, sans-serif';

            // Füge den Text zum Div hinzu
            div.appendChild(text);

            // Erstelle den Button
            const button = document.createElement('button');
            button.textContent = 'Schließen';
            button.style.marginTop = '20px';
            button.style.padding = '10px 20px';
            button.style.fontSize = '16px';
            button.style.cursor = 'pointer';
            button.style.backgroundColor = '#ff4d4d';
            button.style.border = 'none';
            button.style.color = 'white';
            button.style.borderRadius = '5px';

            // Füge die Schließfunktion zum Button hinzu
            button.onclick = function() {
                div.remove();  // Entfernt das Div-Element, wenn der Button geklickt wird
            };

            // Füge den Button zum Div hinzu
            div.appendChild(button);

            // Füge das Div zum Body der Seite hinzu
            document.body.appendChild(div);
        }

    </script>
</body>
</html>