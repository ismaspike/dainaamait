document.getElementById("formularioRegistro").addEventListener("submit", comprobar);

function comprobar(evento) {
    let usuario = document.getElementById("registeruser");
    let pass1 = document.getElementById("registerpass");
    let pass2 = document.getElementById("registerpass2");
    let terminos = document.getElementById("terminos");
    let mensaje = "ERROR\n\n";
    let validado = true;

    if (usuario.value.length < 4) {
        validado = false;
        mensaje += ("-Su usuario debe de tener al menos 4 caraceres\n");
        document.getElementById("labeluser").style.color = "red";
    }
    else if (usuario.value.length > 19) {
        validado = false;
        mensaje += ("-Su usuario debe de tener menos de 20 caraceres\n");
        document.getElementById("labeluser").style.color = "red";
    }
    else if (/^[a-zA-Z0-9- ]*$/. test(usuario.value) == false) {
        validado = false;
        mensaje += ("-Su usuario no debe de contener caracteres especiales\n");
        document.getElementById("labeluser").style.color = "red";
    }
    else {
        document.getElementById("labeluser").style.color = "";
    }
    if (pass1.value != pass2.value) {
        validado = false;
        mensaje += ("-Las contraseñas deben de ser iguales\n");
        document.getElementById("labelpass2").style.color = "red";
    }
    else {
        document.getElementById("labelpass2").style.color = "";
    }

    if (pass1.value.length < 8) {
        validado = false;
        mensaje += ("-La contraseña debe de tener al menos 8 caracteres\n");
        document.getElementById("labelpass").style.color = "red";
    }
    else if (pass1.value.length > 22) {
        validado = false;
        mensaje += ("-La contraseña debe de tener menos de 23 caracteres\n");
        document.getElementById("labelpass").style.color = "red";
    }
    else {
        document.getElementById("labelpass").style.color = "";
    }

    if (!terminos.checked) {
        validado = false;
        mensaje += "-Debe aceptar los términos y condiciones\n";
        document.getElementById("labelterminos").style.color = "red";
    }
    else {
        document.getElementById("labelterminos").style.color = "";
    }
    if (validado) {
        alert("Se ha registrado correctamente con el nombre de usuario "+toLowerCase(document.getElementById("registeruser").value));
    }
    else {
        evento.preventDefault();
        alert(mensaje);
    }

}
