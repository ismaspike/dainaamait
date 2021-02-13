<!--PÁGINA PARA CERRAR SESIÓN-->
<?php
    //Inicio la sesión
    Session_start();

    //En caso de que la sesión exista, procedemos a cerrarla y devolvemos al usuario
    //a la página en la que se encontrara.
    if (isset($_SESSION['name'])) {
        Session_destroy();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    //Si la sesión no estaba iniciada, llevamos al usuario a la página de error con
    //un mensaje personalizado
    else {
        header("Location: error.php?mensaje=No puede cerrar sesión si no tiene una sesión activa.");
        exit;
    }

?>