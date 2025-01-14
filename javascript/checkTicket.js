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