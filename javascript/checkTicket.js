document.getElementById("checkData").addEventListener('click', async function() {
    document.getElementById("check").style.display = "flex";
    let gesSumme = 0;

    // Käuferinformationen festlegen
    checkIds = ['name','vorname','email','telNumber','age','klasse','cntTickets','name01','prename01','mail01','age01'];
    overwriteIds = ['lastname0','prename0','mail0','telNr0','age0','claas0','count0','lastnameCheck01','prenameCheck01','mailCheck01','ageCheck01'];

    for(i = 0;i<checkIds.length;i++){
        if(document.getElementById(checkIds[i]).value == ''){
            document.getElementById(overwriteIds[i]).innerText = "-";
        }else{
            document.getElementById(overwriteIds[i]).innerText = document.getElementById(checkIds[i]).value;
        }
    }

    const ticketCount = parseInt(document.getElementById("cntTickets").value);

    if (ticketCount === 2) {
        // Ticket 02 anzeigen 
        document.getElementById("moneyTicket02").style.display = "flex";

        if(window.innerWidth <= 768){
            document.getElementById("checkTicket02").style.display = "grid";
        }else{
            document.getElementById("checkTicket02").style.display = "flex";
        }
        
        // Ticket 02 Infos festlegen
        document.getElementById("lastnameCheck02").innerText = document.getElementById("name02")?.value || '';
        document.getElementById("prenameCheck02").innerText = document.getElementById("prename02")?.value || '';
        document.getElementById("mailCheck02").innerText = document.getElementById("mail02")?.value || '';
        document.getElementById("ageCheck02").innerText = document.getElementById("age02")?.value || '';

        // Preise abrufen
        let price01 = null;
        let price02 = null;

        for (let index = 0; index < 2; index++) {
            let prename = document.getElementById("prename0" + (index + 1)).value.trim();
            let lastname = document.getElementById("name0" + (index + 1)).value.trim();

            if(index == 0){
                price01 = await getTicketPrice(prename, lastname);
            }else{
                price02 = await getTicketPrice(prename, lastname);
            }
        }

        const gesSumme = price01 + price02;
        document.getElementById("moneyTicket01").innerHTML = "Ticket 1: " + price01 + "€";
        document.getElementById("moneyTicket02").innerHTML = "Ticket 2: " + price02 + "€";
        document.getElementById("moneyBoxSum").innerText = "Summe: " + gesSumme + "€";
    } else {
        // Ticket 02 ausblenden
        document.getElementById("checkTicket02").style.display = "none";
        document.getElementById("moneyTicket02").style.display = "none";

        let price01 = null;

        for (let index = 0; index < 1; index++) {
            let prename = document.getElementById("prename01").value.trim();  // Hier holen wir den Wert des Eingabefeldes und trimmen ihn
            let lastname = document.getElementById("name01").value.trim();  
            price01 = await getTicketPrice(prename, lastname);
        }

        document.getElementById("moneyTicket01").innerHTML = "Ticket 1: " + price01 + "€";
        document.getElementById("moneyBoxSum").innerText = "Summe: " + price01 + "€";
    }

    document.getElementById("manipulateData").style.display = "flex";
    document.getElementById("sendData").style.display = "flex";
});

makeKaeufer_ticket_ids = ['name','vorname','email','age'];
ticketFields = ['name01','prename01','mail01','age01'];
disableCheckbox();

function checkAllFieldsFilled(){
    return makeKaeufer_ticket_ids.every(id => {
        const element = document.getElementById(id);
        return element && element.value.trim() !== ''; // Überprüft, ob das Feld nicht leer ist
    });
}


function makeInputVisible(action){
    if(!action){
        disableCheckbox();
        return;
    }
    armCheckbox();
}

function setFields(){
    makeKaeufer_ticket_ids.forEach((id, index) => {
        const element = document.getElementById(id);
        const ticketField = document.getElementById(ticketFields[index]);
        if (element && (element.value !== '' && element.value !== null && element.value !== undefined)) {            
            ticketField.value = element.value;
        } else {
            console.warn(`Element mit ID "${id}" ist leer oder nicht vorhanden.`);
        }
    });

    ticketFields.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', () => {
                disarmCheckbox();
            });
        } else {
            console.log("Oh nein, Element mit ID " + id + " wurde nicht gefunden.");
        }
    });
}

function checkOnOnload(){
    makeKaeufer_ticket_ids.forEach(id => {
        // Eingaben in die ausgewählten Input-Felder
        const element = document.getElementById(id);
        if (element) {
            if (checkAllFieldsFilled()) {
                makeInputVisible(true);
            }else{
                makeInputVisible(false);
            }
        } else {
            console.warn(`Element mit ID "${id}" nicht gefunden.`);
        }
    });
}

function disableCheckbox(){
    let input = document.getElementById('kaeufer_ticket');
        input.setAttribute('disabled','');
        input.style.cursor = 'not-allowed';
        input.checked = false;
}

function disarmCheckbox(){
    let input = document.getElementById('kaeufer_ticket');
    input.checked = false;
    makeTippy();
}

function armCheckbox(){
    let input = document.getElementById('kaeufer_ticket');
    input.removeAttribute('disabled');
    input.style.cursor = 'pointer';
}

document.getElementById('kaeufer_ticket').addEventListener('click', function(){
    // Checkbox got clicked
    setFields();
});

makeKaeufer_ticket_ids.forEach(id => {
    // Eingaben in die ausgewählten Input-Felder
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('input', () => { // "input" wird ausgelöst, wenn sich der Wert ändert
            if (checkAllFieldsFilled()) {
                makeInputVisible(true);
            }else{
                makeInputVisible(false);
            }
        });
    } else {
        console.warn(`Element mit ID "${id}" nicht gefunden.`);
    }
});

// Ticket CNT Event Listener
document.getElementById('cntTickets').addEventListener('change', () => {
    disarmCheckbox();
});

checkOnOnload();