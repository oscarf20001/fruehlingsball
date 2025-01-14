// Sicherstellen, dass das DOM vollständig geladen ist
document.addEventListener("DOMContentLoaded", function() {
    const cntTicketsInput = document.getElementById("cntTickets");
    const ticketsContainer = document.getElementById("ticketsContainer");
    const ticketCountDisplay = document.getElementById("ticketCountDisplay");

    document.getElementById("manipulateData").style.display = "none";
    document.getElementById("sendData").style.display = "none";

    function generateTickets(count) {

        // BEI NEUAUFRUF DER FUNKTION ALLE GENERIERTEN TICKETS LÖSCHEN UND NEUE ERSTELLEN
        ticketsContainer.innerHTML = '';

        // ANZHAL DER TICKETS AUSGEBEN UND RICHTIG STELLEN
        if(isNaN(count)){
            ticketCountDisplay.innerText = 0;
        }else if(count > 2){
            ticketCountDisplay.innerText = 2;
        }else{
            ticketCountDisplay.innerText = parseInt(count);
        }

        // TICKETS GENERIEREN
        if(count > 1){
            for (let i = 1; i < 3; i++) {
                const ticketDiv = document.createElement("div");
                ticketDiv.classList.add("ticket");
                ticketDiv.innerHTML = `
                <div class="ticket">
                    <h3 id="headlineTicket0${i}">Ticket Nr. <span>${i}</span></h3>
                    <div class="input-field ticketName">
                        <input type="text" id="name0${i}" name="ticketName${i}" required>
                        <label for="ticketName${i}">Nachame:<sup>*</sup></label>
                    </div>
                    <div class="input-field ticketVorName">
                        <input type="text" id="prename0${i}" name="ticketVorName${i}" required>
                        <label for="ticketVorName${i}">Vorname:<sup>*</sup></label>
                    </div>
                    <div class="input-field ticketEmail">
                        <input type="email" id="mail0${i}" name="ticketEmail${i}" required>
                        <label for="ticketEmail${i}">Email:<sup>*</sup></label>
                    </div>
                    <div class="input-field ticketAge">
                        <input type="text" id="age0${i}" name="ticketAge${i}" required>
                        <label for="ticketAge${i}">Alter:<sup>* Zum Zeitpunkt des Balls</sup></label>
                    </div>
                </div>
                `;
                ticketsContainer.appendChild(ticketDiv);
            }
        }else if(count = 1){
            const ticketDiv = document.createElement("div");
            ticketDiv.classList.add("ticket");
            ticketDiv.innerHTML = `
                <div class="ticket">
                    <h3 id="headlineTicket01">Ticket Nr. <span>${count}</span></h3>
                    <div class="input-field ticketName">
                        <input type="text" id="name01" name="ticketName${count}" required>
                        <label for="ticketName${count}">Nachame:<sup>*</sup></label>
                    </div>
                    <div class="input-field ticketVorName">
                        <input type="text" id="prename01" name="ticketVorName${count}" required>
                        <label for="ticketVorName${count}">Vorname:<sup>*</sup></label>
                    </div>
                    <div class="input-field ticketEmail">
                        <input type="email" id="mail01" name="ticketEmail${count}" required>
                        <label for="ticketEmail${count}">Email:<sup>*</sup></label>
                    </div>
                    <div class="input-field ticketAge">
                        <input type="text" id="age01" name="ticketAge${count}" required>
                        <label for="ticketAge${count}">Alter:<sup>* Zum Zeitpunkt des Balls</sup></label>
                    </div>
                </div>
            `;
            ticketsContainer.appendChild(ticketDiv);
        }else{
            console.log("Ungültige Anzahl");
        }
    }

    cntTicketsInput.addEventListener("input", function() {
        const ticketCount = parseInt(cntTicketsInput.value);
        generateTickets(ticketCount);
    });

    generateTickets(parseInt(cntTicketsInput.value));
});