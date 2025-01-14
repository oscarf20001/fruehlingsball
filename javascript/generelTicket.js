// OVERFLOW HERRAUSFINDEN
const elements = document.querySelectorAll('*'); // Wählt alle Elemente aus

elements.forEach(element => {
    const rect = element.getBoundingClientRect(); // Hol die Position und Dimension des Elements
    if (rect.width > window.innerWidth || rect.height > window.innerHeight) {
        console.log('Overflow Element:', element); // Gibt das Element in der Konsole aus
    }
});

if(window.innerWidth <= 768){
    document.getElementById('HeadlineCheck').innerHTML = "Bitte überprüfen:"
    document.getElementById('checkKäuferClaas').innerHTML = "Klasse"
}else{
    document.getElementById('HeadlineCheck').innerHTML = "Bitte überprüfen Sie ihre eingegebenen Daten:"
    document.getElementById('checkKäuferClaas').innerHTML = "Klasse (wenn Schüler des MCG):"
}


document.getElementById("manipulateData").addEventListener('click', function(){
    document.getElementById("check").style.display = "none";
})

document.getElementById("sendData").addEventListener('click', function(){
    document.getElementById("check").style.display = "none";
})