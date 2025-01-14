function closeTicketing(){
    const classLists = ["name", "vorname", "email", "telNumber", "age", "klasse", "cntTickets", "name01", "prename01", "mail01", "age01", "checkData"];

    for(i = 0;i<classLists.length;i++){
        document.getElementById(classLists[i]).disabled = true;
        document.getElementById(classLists[i]).classList.add('closed');
    }
    document.getElementById("checkData").classList.add("closedHover");

    //CREATE CLOSING BANNER
    const banner = document.createElement("div");
    banner.classList.add("closingBanner");
    banner.innerHTML = "Aktuell ist der Ticketshop geschlossen! Bei wichtigen Anfragen melden Sie sich bitte bei dieser Email-Adresse: streiosc@curiegym.de";

    //CLOSING BANNER HINZUFÜGEN
    const body = document.querySelector('body');
    body.prepend(banner);
}