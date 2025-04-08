<!-- ADMIN PANEL -->
<?php
include '../affiliations/php/db_connection.php';

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Panel Frühlingsball des MCG's</title>
    <link rel="stylesheet" href="../styles/admin.css">
    <script src="../javascript/searchMails.js" defer></script>
</head>
<body>
    <div id="master-headline">
        <h1>Herzlich Willkommen</h1>
        <p>Im cranzytastischen Adminpanel für den Frühlingsball des MCGs 2025</p>
    </div>
    <div id="getTicketInfo">
        <div id="requestTicket">
            <h2>Ein Käufer und seine Tickets anfordern</h2>
            <p>Hier Email des Käufers eintragen. Käufer ist eineindeutig über Email identifizierbar!</p>
            <div id="outer-form">
                <form action="admin.php" method="POST" id="requestTicket-F">
                    <div class="formLeft">
                        <input type="hidden" name="form_type" value="form1">
                        <div class="input-field email">
                            <input type="email" name="email" id="f-email" required onkeyup="searchMails()">
                            <label for="email">Email</label>
                        </div>
                    </div>

                </form>
                <div class="formRight">
                    <input type="submit" value="Käufer suchen" form="requestTicket-F">
                </div>
            </div>
        </div>

        <div id="suggestions"></div>

        <div id="resultTicket">
            <div id="käufer-data">
                <table style="width: 100%;" id="Table-Kaeufer">
                    <tbody>
                        <!-- ============ PHP ============= -->

                        <?php 

                        //GET KÄUFER DATA TROUGH MAIL
                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                if (isset($_POST['form_type'])) {
                                    if ($_POST['form_type'] === 'form1') {
                                        // GET MAIL
                                        $k_getMail = htmlspecialchars($conn->real_escape_string(trim($_POST["email"])));

                                        // USE SQL TO SEARCH FOR MAIL IN DB
                                        $sqlSearchMail = "SELECT * FROM `käufer` WHERE `email` = ?";
                                        $stmt = $conn->prepare($sqlSearchMail);
                                        $stmt->bind_param("s", $k_getMail);
                                        $stmt->execute();

                                        $result = $stmt->get_result();

                                        if($result->num_rows == 0){echo "Keine Ergebnisse für diese Mail gefunden";die;}
                                        
                                        if ($result->num_rows < 2) {
                                            while ($row = $result->fetch_assoc()) {
                                                $k_prename = $row["vorname"];
                                                $k_name = $row["nachname"];
                                                $k_mail = $row["email"];
                                                $k_age = $row["age"];
                                                $k_telNr = $row["telNr"];
                                                $k_class = $row["klasse"];
                                                $k_cntTickets = $row["cntTickets"];
                                                $k_open = $row["open"];
                                                $k_status = $row["status"];
                                                // Füge hier weitere Spalten hinzu, die du ausgeben möchtest
                                            }

                                            echo "<tr class='header'>
                                                    <td>Vorname</td>
                                                    <td>Nachname</td>
                                                    <td>Email</td>
                                                    <td>Telefonnummer</td>
                                                    <td>Alter</td>
                                                    <td>Klasse</td>
                                                    <td>Anzahl Tickets</td>
                                                    <td>noch offene Kosten</td>
                                                    <td>Status</td>
                                                </tr>
                                                <tr class='k-tr'>
                                                    <td id='k-prename'>{$k_prename}</td>
                                                    <td id='k-name'>{$k_name}</td>
                                                    <td id='k-mail'>{$k_mail}</td>
                                                    <td id='k-tel'>{$k_telNr}</td>
                                                    <td id='k-age'>{$k_age}</td>
                                                    <td id='k-class'>{$k_class}</td>
                                                    <td id='k-cntTickets'>{$k_cntTickets}</td>
                                                    <td id='k-sum'>{$k_open}€</td>
                                                    <td id='k-status' class='status{$k_status}'><div class=" . 'circle' . "></div></td>
                                                </tr>";

                                            $stmt->close();
                                            
                                        } else if($result->num_rows < 1){
                                            echo "Keine Ergebnisse für die E-Mail-Adresse gefunden.";
                                            die;
                                        }else{
                                            echo "Mehr als einen Käufer für diese Email gefunden";
                                            die;
                                        }
                                    }
                                }
                            }
                        //DISPLAY THEM

                        ?>

                        <!-- ============ PHP ============= -->
                    </tbody>
                </table>
            </div>
            <div id="ticket-data">
                <table style="width: 100%;">
                    <tbody>
                        <tr class="header">
                            <td>Ticket</td>
                            <td>Vorname</td>
                            <td>Nachname</td>
                            <td>Email</td>
                            <td>Alter</td>
                            <td>Kosten</td>
                        </tr>
                        
                        <!-- ============ PHP ======== -->

                        <?php 
                         // GET ID OF Käufer
                         if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            if (isset($_POST['form_type'])) {
                                if ($_POST['form_type'] === 'form1') {
                                    $sqlGetKaeuferId = "SELECT ID FROM käufer WHERE email = '".$_POST["email"]."';";
                                    $stmt = $conn->prepare($sqlGetKaeuferId);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $k_id = $result->fetch_assoc();
                                    $k_id = $k_id['ID'];
                                    $stmt->close();

                                    //FOR FURHTER OPERATIONS WITH ITTERATIONS
                                    //$k_cntTickets;
                                    //GET ALL TICKETS ON THIS ID 
                                    $sqlGetAllTickets = "SELECT * FROM tickets WHERE käufer_ID = ". $k_id .";";
                                    $stmt = $conn->prepare($sqlGetAllTickets);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if($result->num_rows > 0){
                                        $ticketIndex = 1;
                                        while($ticket = $result->fetch_assoc()){
                                            echo "<tr>
                                                    <td id='t{$ticketIndex}-nr'>" . $ticketIndex .".</td>
                                                    <td id='t{$ticketIndex}-prename'>" . htmlspecialchars($ticket['vorname']) . "</td>
                                                    <td id='t{$ticketIndex}-name'>" . htmlspecialchars($ticket['nachname']) . "</td>
                                                    <td id='t{$ticketIndex}-mail'>" . htmlspecialchars($ticket['email']) . "</td>
                                                    <td id='t{$ticketIndex}-age'>" . htmlspecialchars($ticket['age']) . "</td>
                                                    <td id='t{$ticketIndex}-price'>" . htmlspecialchars($ticket['sum']) . "€</td>
                                                </tr>";
                                            $ticketIndex++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='6'>Keine Tickets gefunden</td></tr>";
                                    }
                                }
                            }
                         }
                        ?>                        

                        <!-- ============ PHP ======== -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="checkOutTicket">
            <h2>Einen Käufer abrechnen</h2>
            <p>Hier den Betrag eingeben, welchen der Käufer beglichen hat. Die beglichenen Kosten werden in den Datensatz der vorher eingegebenen Email hinzugefügt. Zusätzlich bitte unbedingt die Methode auswählen => sonst keine Mail an Käufer.</p>
            <form class="sendMoneyContainer" id="sendMoneyForm">
                <div class="input-field euro">
                    <input type="number" name="t-paid" id="t-paid" step="0.01" min="0" required>
                    <label for="euro">Bezahlt (in Euro):</label>
                </div>
                <div class="input-field selectOptions">
                    <select id="options" name="options" required>
                        <option value="" disabled selected>-</option>
                        <option value="Bar">Bar</option>
                        <option value="Überweisung">Überweisung</option>
                    </select>
                    <label for="options">Wähle eine Option:</label>
                </div>
                <input type="button" value="Kosten checken!" id="pre_checkout_btn">
            </form>
        </div>
    </div>

    <div class="check" id="checkWindow">
        <h1>Oho welch <span style="color:#52c393;">fancytastische</span> Aktion!</h1>
        <h3>Du bist gerade dabei einen <span style="color:#52c393;">wichtigen Datensatz</span> zu verändern. Überprüfe bitte vorher deine angebenen Daten, damit keine <span style="color:#52c393;">Fehler</span> passieren.<br><br></h3>
        <table>
            <tr>
                <td>Vorname:</td>
                <td id="c-prename"></td>
            </tr>
            <tr>
                <td>Nachname:</td>
                <td id="c-name"></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td id="c-mail"></td>
            </tr>
            <tr>
                <td>Alter:</td>
                <td id="c-age"></td>
            </tr>
            <tr>
                <td>Methode:</td>
                <td id="c-method"></td>
            </tr>
            <tr>
                <td>Bezahlt:</td>
                <td id="c-sum"></td>
            </tr>
        </table>
        <div class="btns">
            <input type="button" value="Korrigieren!" id="correction_btn">
            <input type="button" value="Senden!" id="checkout_btn">
        </div>
    </div>
    <div class="downerContainer">
        <div class="extraMail">
            <div class="mail">
                <p class="controll_Service_Description controll_Service_Description_Mail">Send extra Mail</p>
                <input type="submit" value="Resend Mails" id="r_mail" class="controll_Service_Button">
            </div>
        </div>
        <div class="openEntrance controllUnit">
            <div class="entranceControlls controlls">
                <div id="" class="st-light_container">
                    <div id="statusLightEinlass_downer" class="st-light"></div>
                </div>
                <p class="controll_Service_Description">Einlass</p>
                <input type="submit" value="Einlass öffnen" id="r_openEinlass" class="controll_Service_Button">
            </div>
        </div>
        <div class="openShop controllUnit">
            <div class="shopControlls controlls">
                <div id="" class="st-light_container">
                    <div id="statusLightShop_downer" class="st-light"></div>
                </div>
                <p class="controll_Service_Description">Shop</p>
                <input type="submit" value="Shop öffnen" id="r_openShop" class="controll_Service_Button">
            </div>
        </div>
    </div>
    

    <script>

        async function checkEntrance(service, request) {
            console.log("Hallo");

            try {
                const response = await fetch('switchEntranceStatus.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `service=${service}&request=${request}`
                });

                const data = await response.json();

                if (data && (data.new_stat == 1 || data.status == 1)) {
                    document.getElementById(`r_open${service}`).value = `${service} schließen`;
                    document.getElementById(`statusLight${service}_downer`).style.background = 'green';
                    document.getElementById(`statusLight${service}_downer`).style.animation = 'glow1 1.5s infinite alternate';
                } else {
                    document.getElementById(`r_open${service}`).value = `${service} öffnen`;
                    document.getElementById(`statusLight${service}_downer`).style.background = 'red';
                    document.getElementById(`statusLight${service}_downer`).style.animation = 'glow2 1.5s infinite alternate';
                }
            } catch (error) {
                console.error('Fehler:', error);
                alert('Es gab einen Fehler');
            }
        }

        // Klick-Event Listener für "Einlass"
        document.getElementById('r_openEinlass').addEventListener('click', function() {
            checkEntrance('Einlass', 'switch');
        });

        // Klick-Event Listener für "Shop"
        document.getElementById('r_openShop').addEventListener('click', function() {
            checkEntrance('Shop', 'switch');
        });

        // Laden der Seite, um die Anfragen beim Laden auszuführen
        window.addEventListener('load', async function() {
            await checkEntrance('Einlass', 'get');
            await checkEntrance('Shop', 'get');
        });

        document.getElementById('r_mail').addEventListener('click', function(){
            window.location.href = 'new-mail/index.php';
        });

        document.getElementById('pre_checkout_btn').addEventListener('click', function(){
            let checkWindow = document.getElementById('checkWindow');
            checkWindow.style.display = 'flex';

            console.log(document.getElementById('options').value)

            document.getElementById('c-prename').textContent = document.getElementById('k-prename').textContent;
            document.getElementById('c-name').textContent = document.getElementById('k-name').textContent;
            document.getElementById('c-mail').textContent = document.getElementById('k-mail').textContent;
            document.getElementById('c-age').textContent = document.getElementById('k-age').textContent;
            document.getElementById('c-method').textContent = document.getElementById('options').value;
            document.getElementById('c-sum').textContent = document.getElementById('t-paid').value + "€";
        });

        document.getElementById('correction_btn').addEventListener('click', function () {
            document.getElementById('checkWindow').style.display = 'none';
        });

        document.getElementById('checkout_btn').addEventListener('click', function () {
            document.getElementById('checkWindow').style.display = 'none';
            const paidValue = document.getElementById('t-paid').value;
            const email = document.getElementById('k-mail').textContent;
            const method = document.getElementById('options').value;
            const name = document.getElementById('k-prename').textContent;
            const userCookie = getCookie('User');

            if(userCookie){
                console.log("Benutzername aus Cookie: " + userCookie);
            }else{
                console.log("Cookie 'User' nicht gefunden!");
            }

            fetch('checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `paid=${encodeURIComponent(paidValue)}&email=${encodeURIComponent(email)}&method=${encodeURIComponent(method)}&user=${encodeURIComponent(userCookie)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data) {
                    // HTML-Felder aktualisieren
                    document.getElementById('k-sum').textContent = `${data.open}€`; // Offener Betrag

                    // Status aktualisieren
                    const statusElement = document.getElementById('k-status');
                    statusElement.textContent = ''; // Alten Status entfernen
                    statusElement.className = `status${data.status}`;
                    const statusCircle = document.createElement('div');
                    statusCircle.className = 'circle';
                    statusElement.appendChild(statusCircle);

                    // Alert zur Bestätigung
                    alert('Daten erfolgreich aktualisiert!');

                    //WITH ONCLICK ON THIS BUTTON A FRUTHER FILE HAS TO BE EXECUTED. 
                    //CHECK IF, WHEN CUSTOMER HAS PAID, THE OPEN FIELD EQUALS 0. IF SO, SEND A MAIL, THAT ALL COSTS ARE 0
                    if (data.open == 0) {
                        console.log(email)
                        fetch('../mail/finalMail.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ email:email})
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Fehler bei finalMail.php');
                            }
                            return response.json();
                        })
                        .catch(error => {
                            console.error('Fehler bei der finalMail-Anfrage:', error);
                        });
                    }
                } else {
                    alert('Fehler beim Verarbeiten der Daten.');
                }
            })
            .catch(error => {
                console.error('Fehler:', error);
                alert('Fehler bei der Kommunikation mit dem Server.');
            });
        });

        function getCookie(name){
            let value = "; " + document.cookie;
            let parts = value.split("; " +  name + "=")
            if(parts.length === 2){
                return parts.pop().split(";").shift()
            }
            return null;
        }
    </script>