<!--PÁGINA BACKEND PARA LA CREACIÓN DE PARTIDA-->
<?php

  //CREACIÓN DE VARIABLES
  include '..\..\seguridad\dainaamait\dbaccess.php';
  $sesion = "";
  $sesion2 = "";

  session_start();
  $partida = "";
  $tablero = "";
  $idpartida;
  $cerrar_sesion = "";

  //Compruebo que la sesión está iniciada, ya que solamente se puede buscar partida
  //si la sesión está iniciada
  if (!isset($_SESSION['name'])) {
      header('Location: error.php?mensaje=No puede buscar una partida sin estar registrado.');
      exit;
  }
  //Compruebo que me hayan pasado un usuario contra el que jugar la partida. En caso
  //contrario, devolveré al usuario a la página para que escriba el nombre del rival
  else if(!isset($_POST['usuarioBusqueda'])) {
      header('Location: buscarPartida.php');
      exit;
  }

  //Si todo lo anterior va bien, comienzo con la búsqueda de la partida
  else {
    $usuario = $_SESSION['name'];
    $rival = $_POST['usuarioBusqueda'];
    $rival = mb_strtolower($rival,'UTF-8');

    //Compruebo que el jugador no intenta jugar contra sí mismo
    if ($usuario == $rival) {
        header('Location: buscarPartida.php?error=Es un poco triste jugar contra sí mismo al tres en raya.');
        exit;
    }


    $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

    //Consulta en la que sacaré si el usuario rival existe
    $sql = 'select NombreUsuario from usuarios where BINARY NombreUsuario = "'.$rival.'"';
    $consulta = mysqli_prepare($canal, $sql);
    if (!$consulta) {
      echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
      exit;
    }
    mysqli_stmt_execute($consulta);
    mysqli_stmt_store_result($consulta);
    mysqli_stmt_fetch($consulta);
    $encontrados = mysqli_stmt_num_rows($consulta);

    //En caso de que el rival no exista, le devolveré a la página anterior con un mensaje de error
    if ($encontrados == 0) {
        header('Location: buscarPartida.php?rival='.$rival.'&error=Usuario no existente.');
        exit;
    }

    else {
        mysqli_stmt_close($consulta);

        //Ahora comprobaré que esos dos usuarios no tienen activa alguna otra partida
        $sql = 'select idpartida from partidas where (jugadorx ="'.$rival.'" and jugadory = "'.$usuario.'" and victoria is null) or (jugadorx ="'.$usuario.'" and jugadory = "'.$rival.'" and victoria is null)';
        $consulta = mysqli_prepare($canal, $sql);
        if (!$consulta) {
        echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
        exit;
        }
        mysqli_stmt_execute($consulta);
        mysqli_stmt_store_result($consulta);
        mysqli_stmt_fetch($consulta);
        $encontrados = mysqli_stmt_num_rows($consulta);
        
        //En caso de que tengan alguna partida activa, le devolveré a la página anterior con
        //un mensaje de error
        if ($encontrados > 0) {
            header('Location: buscarPartida.php?error=Ya tiene una partida activa contra ese usuario.');
            exit;
        }
        else {
            mysqli_stmt_close($consulta);

            //Si se han pasado todas las condiciones actuales, comenzamos con la creación
            //de la partida. Guardaré en una variable qué skin (ficha) tiene el usuario
            $sql = "select SkinCustom from usuarios where NombreUsuario='".$usuario."'";
    
            $consulta = mysqli_prepare($canal, $sql);
        
            
            if (!$consulta) {
                echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                exit;
            }
            
            mysqli_stmt_execute($consulta);
            mysqli_stmt_store_result($consulta);
            mysqli_stmt_bind_result($consulta, $fichax);
            mysqli_stmt_fetch($consulta);

            //Posteriormente, guardaré en otra variable qué skin tiene el rival
            $sql = "select SkinCustom from usuarios where NombreUsuario='".$rival."'";
    
            $consulta = mysqli_prepare($canal, $sql);
        
            
            if (!$consulta) {
                echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                exit;
            }
            
            mysqli_stmt_execute($consulta);
            mysqli_stmt_store_result($consulta);
            mysqli_stmt_bind_result($consulta, $fichay);
            mysqli_stmt_fetch($consulta);

            //Con esta consulta, crearé la partida con todos los valores predeterminados
            $sql = 'insert into partidas(fichas, logox, logoy, jugadorx, jugadory, turno, proxturno, fichaturno, victoria, monedas) values ("ZZZZZZZZZ","'.$fichax.'","'.$fichay.'","'.$usuario.'","'.$rival.'","'.$usuario.'","'.$rival.'","X",NULL,0)';
            $consulta = mysqli_prepare($canal, $sql);
            echo $sql;
        
            
            if (!$consulta) {
                //echo $fichax;
                echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                exit;
            }
            else {
                mysqli_stmt_execute($consulta);
                mysqli_stmt_close($consulta);

                $sql = 'select idpartida from partidas where turno = "'.$usuario.'" and proxturno = "'.$rival.'"';
                $consulta = mysqli_prepare($canal, $sql);
        
                if (!$consulta) {
                    echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                    exit;
                }
            
                mysqli_stmt_execute($consulta);
                mysqli_stmt_store_result($consulta);
                mysqli_stmt_bind_result($consulta, $idpartida);
                mysqli_stmt_fetch($consulta);

                //Tras crear la partida, llevaré al usuario a la partida. El que crea la partida
                //siempre será la ficha X y también será quien empieza a jugar
                header("Location: tablero.php?idpartida=".$idpartida);
                exit;

                
        }
    }
  }
}
?>