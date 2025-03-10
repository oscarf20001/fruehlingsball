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
</head>
<body>
    <h1>Mails erneut senden!</h1>
    <p>Hier gibts die Möglichkeit, Mails erneut zu versenden. Wenn eine Person eine Mail nicht bekommen hat, kann man diese hier erneut senden. Dafür die Mail in das Feld unten eintragen und wählen, welche Mail erneut gesendet werden soll!</p>
    <input type="email" name="email" id="email">
    <select name="type" id="typeOfMail">
        <option value="none" disabled selected>-- Bitte auswählen --</option>
        <option value="registration">Reservierungsmail</option>
        <option value="submitation">Bestätigungsmail</option>
        <option value="ticket">Ticket</option>
    </select>
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

                case 'ticket':
                    //action
                    console.log('We have to send third Email / QR-Code bzw. Infos');
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
        }

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
                return [data.mail_type,data.mail]; // Der Wert wird jetzt direkt zurückgegeben
            } catch (error) {
                console.error('Es gab ein Problem mit der Fetch-Operation:', error);
                return null; // Oder du kannst einen Standardwert zurückgeben, falls ein Fehler auftritt
            }
        }


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

                default:
                    msg = 'Unknown/Unexpected Error'
                    console.error('Unknown/Unexpected Error');
                    break;
            }
        }
    </script>
</body>
</html>