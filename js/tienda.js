let skinactual = document.getElementById("skinactual").value;

let fichas = document.getElementsByName("ficha");
console.log(fichas.length);
    for (let a = 0; a < fichas.length; a++) {
        console.log(fichas[a].value);
        if (fichas[a].value == skinactual) {
            document.getElementsByClassName("botonSubmit")[a].disabled="disabled";
        }
    }