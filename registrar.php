<?php
    //PÁGINA DE REGISTRO DE USUARIO

    //Compruebo que todos los campos del formulario se han rellenado. En caso
    //contrario, devuelvo al usuario a la página de error
    if (!isset($_POST['registeruser']) || !isset($_POST['registerpass']) || !isset($_POST['registerpass2']) || !isset($_POST['terminos'])) {
        header("Location: error.php?mensaje=Ha ocurrido un error con la creación.");
        exit;
    }

    //Creo la variable $error que será la que activará o no activará el procedimiento
    //en caso de que haya habido algún error
    $error = false;

    //Creo variables con los campos de registro
    $usuario = trim($_POST['registeruser']);
    $usuario = mb_strtolower($usuario,'UTF-8');
    $pass1 = trim($_POST['registerpass']);
    $pass2 = trim($_POST['registerpass2']);
    $terminos = trim($_POST['terminos']);

    //Compruebo que las contraseñas son iguales (la normal y la de confirmación)
    if ($pass1 != $pass2) {
        $error = true;
    }

    //Compruebo que el tamaño de la contraseña es el correcto
    else if (strlen($pass1) > 22 || strlen($pass1) < 8) {
        $error = true;
    }

    //Compruebo que el tamaño del nombre del usuario es correcto
    if (strlen($usuario) < 4 || strlen($usuario) > 19) {
        $error = true;
    }
    
    //Compruebo que el usuario cumpla el patrón de validación
    if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $usuario)) {
        $error = true;
    }

    //En caso de error, mandaré al usuario a la página de error
    if ($error) {
        header("Location: error.php?mensaje=Ha ocurrido un error con la creación de su usuario.");
        exit;
    }

    //En caso contrario, comenzaré con el proceso para registrar al usuario
    else {
        include '..\..\seguridad\dainaamait\dbaccess.php';
        $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

        if (!$canal) {
            echo "Ha ocurrido el error ".mysqli_connect_errno()." ".mysqli_connect_error()."<br>";
            exit;
        }
        mysqli_set_charset($canal,"utf8");

        //Consulta para comprobar que el nombre de usuario no está
        //en uso
        $sql = "select NombreUsuario from usuarios where NombreUsuario=?";
        
        $consulta = mysqli_prepare($canal, $sql);
        mysqli_stmt_bind_param($consulta, "s", $usuario);
        
        if (!$consulta) {
            echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
            exit;
        }
        
        mysqli_stmt_execute($consulta);
        mysqli_stmt_store_result($consulta);
        $encontrados = mysqli_stmt_num_rows($consulta);
        
        //Si se ha encontrado algún registro, se avisará de que ese
        //usuario ya existe
        if ($encontrados != 0) {
            mysqli_stmt_close($consulta);
            $http = "Location: registro.php?";
            $http .= "usuario=".urlencode($usuario);
            $http .= "&alerta=".urlencode("Usuario ya existente");
            header($http);
            exit;
        }
        else {
            //Pongo el usuario en minúsculas
            
            
            mysqli_stmt_close($consulta);
            
            //En caso de que el usuario no esté repetido, lo guardo en la base
            //de datos con la clave introducida
            $sql = "insert into usuarios (NombreUsuario, Password, Ganadas, Empatadas, Perdidas, SkinCustom, Monedas) values('".$usuario."','".md5($pass1)."', 0,0,0,'normal.png',0)";
            $consulta = mysqli_prepare($canal, $sql);
            if (!$consulta) {
                echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                exit;
            }
            mysqli_stmt_execute($consulta);
            mysqli_stmt_close($consulta);

            //Finalmente, cuando todo es correcto, devuelvo al usuario a la página de index
            //con un mensaje indicando que ha creado su cuenta
            $http = "Location: index.php?";
            $http .= "mensaje=".urlencode("Se ha registrado correctamente con el nombre de usuario ".$usuario.".<br>¡Inicie sesión para empezar a jugar!");
            header($http);
            exit;
        }
    }
?>



