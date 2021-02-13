<?php
    //PÁGINA BACK-END PARA INICIAR SESIÓN

    //Compruebo que el usuario no tiene la sesión ya iniciada
    if (isset($_SESSION['name'])) {
        header("Location: error.php?mensaje=No puede iniciar sesión si ya la tiene iniciada. Si quiere, pulse el botón de cerrar sesión.");
        exit;
    }

    //Compruebo que el usuario ha escrito algo en las casillas de usuario
    //y contraseña. En caso contrario, le devolveré a la página anterior.
    if (!isset($_POST['sessionuser']) || !isset($_POST['sessionpass'])) {
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit;
    }

    //Variable que determinará si el login ha sido incorrecto
    $error = false;

    //Variables que contienen el usuario y la contraseña introducidas
    $usuario = trim($_POST['sessionuser']);
    $usuario = mb_strtolower($usuario,'UTF-8');
    $pass = trim($_POST['sessionpass']);


    //Conecto con la BBDD
    include '..\..\seguridad\dainaamait\dbaccess.php';
    $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

    if (!$canal) {
        echo "Ha ocurrido el error ".mysqli_connect_errno()." ".mysqli_connect_error()."<br>";
        exit;
    }
    mysqli_set_charset($canal,"utf8");

    //Consulta para comprobar que el nombre de usuario no está
    //en uso
    $sql = "select * from usuarios where BINARY NombreUsuario like '".$usuario."' and Password='".md5($pass)."'";
    
    $consulta = mysqli_prepare($canal, $sql);
   
    
    if (!$consulta) {
        echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
        exit;
    }
    
    mysqli_stmt_execute($consulta);
    mysqli_stmt_store_result($consulta);
    $encontrados = mysqli_stmt_num_rows($consulta);
    
    //Si se ha encontrado algún registro, se avisará de que ese
    //usuario aún no está creado
    if ($encontrados == 0) {
        $header = $_SERVER['HTTP_REFERER'];
        if (substr($header, 0, strpos($header, "?"))) {
            $header = substr($header, 0, strpos($header, "?"));
        }

        mysqli_stmt_close($consulta);
        $http = "Location: ".$header;
        $http .= "?sessionuser=".urlencode($usuario);
        header($http);
        exit;
    }
    
    //En caso contrario, la sesión será iniciada
    else {
        mysqli_stmt_close($consulta);
        
        session_start();
        $_SESSION['name'] = $usuario;

        $http = "Location: index.php";
        header($http);
        exit;
        
    }
?>
