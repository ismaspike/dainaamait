let pTurno = document.getElementById("turno");

let mensaje = "Coloque una ficha y pulse sobre jugar: ";
let inputCombinacion = "";
let idPartida = document.getElementById("idPartida").value;
document.getElementById("idPartida").remove();

let formulario = '<input type="submit" value="Jugar" disabled="disabled" id="enviarPartida">';
formulario +=    '<input type="hidden" name="combinacion" id="combinacion" value="fdsaihjfi">';
formulario +=    '<input type="hidden" name="idPartida" value="'+idPartida+'">';

pTurno.innerHTML = mensaje+""+formulario;

let xo = document.getElementById("fichaTurno").value;

console.log(document.getElementById("fichaTurno").value);

let blancos = document.getElementsByClassName("Z");
console.log(blancos);

let activa = null;


ponerEventos();
document.getElementById("formularioTablero").addEventListener("submit", rellenarHidden);


function entrar(evento) {
    console.log(evento.target);
    evento.target.classList.add(xo);
    evento.target.classList.remove("Z");
    evento.target.getElementsByTagName("img")[0].src = "./img/fichas/"+document.getElementById("logoActual").value;
    evento.target.getElementsByTagName("img")[0].style = "opacity: 50%;";
} 

function salir(evento2) {
    evento2.target.classList.add("Z");
    evento2.target.classList.remove(xo);
    evento2.target.getElementsByClassName("logo")[0].src = "./img/fichas/nada.png";
}

function clic(evento3) {
    evento3.target.parentElement.addEventListener("click", segundoClic);
    evento3.target.parentElement.removeEventListener("click", clic);
    evento3.target.parentElement.removeEventListener("mouseenter", entrar);
    evento3.target.parentElement.removeEventListener("mouseleave", salir);
    document.getElementById("enviarPartida").disabled ="";

    //console.log(activa);
    if (activa != null) {
        //console.log(activa);
        activa.getElementsByTagName("img")[0].src = "./img/fichas/nada.png";
        activa.classList.remove(xo);
        activa.classList.add("Z");
        activa.addEventListener("mouseenter", entrar);
        activa.addEventListener("mouseleave", salir);
        activa.addEventListener("click", clic);
        activa.removeEventListener("click", segundoClic)
        
    }
    activa = evento3.target.parentElement;

}

function segundoClic(evento4) {
    activa = null;
    evento4.target.parentElement.removeEventListener("click", segundoClic);
    console.log("Segundo clic");
    evento4.target.parentElement.classList.add("Z");
    evento4.target.parentElement.classList.remove(xo); 
    evento4.target.parentElement.addEventListener("mouseenter", entrar);
    evento4.target.parentElement.addEventListener("mouseleave", salir);
    evento4.target.parentElement.addEventListener("click", clic);
    document.getElementById("enviarPartida").disabled = "disabled";

}

function ponerEventos() {
    blancos = document.getElementsByClassName("Z");
    for (let a = 0; a < blancos.length; a++) {
        blancos[a].addEventListener("mouseenter", entrar);
        blancos[a].addEventListener("mouseleave", salir);
        blancos[a].addEventListener("click", clic);
    }
}


function quitarEventos() {
    blancos = document.getElementsByClassName("Z");
    for (let a = 0; a < blancos.length; a++) {
        blancos[a].removeEventListener("mouseenter", entrar);
        blancos[a].removeEventListener("mouseleave", salir);
    }
}

function rellenarHidden(evento5) {
    inputCombinacion = "";
    for (let a = 0; a < 9; a++) {
        inputCombinacion += document.getElementsByTagName("td")[a].classList[1];
    }
    document.getElementById("combinacion").value = inputCombinacion;
    console.log(inputCombinacion);
}








