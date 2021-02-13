<?php
  include '..\..\seguridad\dainaamait\dbaccess.php';


  session_start();
  $idpartida;


  //Compruebo que la sesión está iniciada
  if (!isset($_SESSION['name'])) {
    $header("index.php");
    exit;
  }
  else {
    $nombreUsuario = $_SESSION['name'];
    $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

    mysqli_set_charset($canal,"utf8");
    
    //Compruebo que el usuario ha llegado aquí enviando la partida de manera normal
    if (isset($_POST['combinacion'])) {
      
      $combinacionEnviada = $_POST['combinacion'];

        //Hago otra comprobación por si acaso el usuario ha llegado aquí por error
        if (!isset($_POST['idPartida'])) {
          header("Location: error.php?mensaje=Ha ocurrido un error. Inténtelo de nuevo más tarde.");
          exit;
        }
        else {
          $idPartida = $_POST['idPartida'];
          
          //Saco todos los datos de la partida, y posteriormente los almacenaré en variables (bind_result)
          $sql = 'select fichas, logox, logoy, jugadorx, jugadory, turno, proxturno, fichaturno, victoria, monedas from partidas where idpartida = "'.$idPartida.'"';
          $consulta = mysqli_prepare($canal, $sql);

          if (!$consulta) {
            echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
            exit;
          }
          
          mysqli_stmt_execute($consulta);
          mysqli_stmt_store_result($consulta);
          mysqli_stmt_bind_result($consulta, $fichas, $logox, $logoy, $jugadorx, $jugadory, $turno, $proxturno, $fichaturno, $victoria, $monedas);
          mysqli_stmt_fetch($consulta);
          $encontrados = mysqli_stmt_num_rows($consulta);
          
          //En caso de que por cualquier razón la partida no exista, devuelvo al usuario a 
          //la página de error con un mensaje personalizado
          if ($encontrados == 0) {
            header("Location: error.php?mensaje=Ha habido un error al enviar la partida. Inténtelo de nuevo más tarde.");
            exit;
          }

          else {
            //Compruebo que le tocaba jugar al usuario. En caso contrario, le devuelvo a la
            //página de error con un mensaje personalizado
            if (strcasecmp($nombreUsuario, $turno) != 0){
              header("Location: error.php?mensaje=Ha ocurrido un error inesperado.");
              exit;
            }
            else {
              $diferencias = 0;
              $fichasZ = 0;

              //Compruebo si era el turno de las X o de las Y
              if  ($jugadorx == $turno) {
                $xy = "X";
              }
              else {
                $xy = "O";
              }

              //Con este bucle comprobaré que la combinación enviada es correcta, ya que modificando
              //la página web se pueden poner dos fichas, mover las anteriores, etc...
              for ($a = 0; $a < 9; $a++) {

                //Primero, comprobaré si el usuario no ha enviado una ficha incorrecta.
                //En caso de que sea así, le mandaré a página de error ya que seguramente ha modificado el tablero.
                if ($combinacionEnviada[$a] != "O" && $combinacionEnviada[$a] != "Z" && $combinacionEnviada[$a] != "X") {
                  header("Location: error.php?mensaje=No manipule el tablero");
                  exit;
                }
                //Aquí comprobaré que el usuario no ha modificado ninguna ficha ya establecida. Es decir, si era una X
                //o un O, que siga siéndolo
                if (($fichas[$a] == "O" || $fichas[$a] == "X") && $fichas[$a] != $combinacionEnviada[$a]) {
                  header("Location: error.php?mensaje=No manipule el tablero.");
                  exit;
                }
                //Comprobaré las diferencias de la combinación de tablero que envía el usuario con
                //la que había previamente
                if ($combinacionEnviada[$a] != $fichas[$a] && $fichas[$a] == "Z") {
                  $diferencias++;
                }
                //También sumaré las fichas en blanco que hay en el tablero
                if ($combinacionEnviada[$a] == "Z") {
                  $fichasZ++;
                }
              }
              
              //Compruebo que se ha modificado una sola casilla del tablero. En caso de que hayan sido más, (o menos),
              //mandaré al usuario a la página de error
              if ($diferencias != 1) {
                header('Location: error.php?mensaje=No manipule el tablero.');
                exit;
              }

              else {
                //Función que sumará monedas, victorias, empates y derrotas al perfil del usuario.
                //Más adelante le hago la llamada, si es que la partida ha finalizado.
                function monedas($user1, $user2, $nombreUsuario, $proxturno, $canal, $idPartida){
                  //Updatewins1 y Updatewins2 será la frase que pongo en la secuencia SQL para indicar qué
                  //tengo que sumarle a cada usuario.
                  $updateWins1 = "Ganadas = Ganadas + 1";
                  $updateWins2 = "Perdidas = Perdidas + 1";
                  //Si las monedas son 50, ambos usuarios han empatado. Si son 100, el usuario con 100
                  //es el que hah ganado.
                  if ($user1 ==  50) {
                    $updateWins1 = "Empatadas = Empatadas + 1";
                    $updateWins2 = "Empatadas = Empatadas + 1";
                  }
                  //Procedo a escribir la sentencia para el user1
                  $sql = 'update usuarios set Monedas = Monedas+'.$user1.', '.$updateWins1.' where NombreUsuario="'.$nombreUsuario.'"';
                  $consulta = mysqli_prepare($canal, $sql);

                  if (!$consulta) {
                    echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                    exit;
                  }
              
                  mysqli_stmt_execute($consulta);
                  //Escribo la consulta para el user2
                  $sql = 'update usuarios set Monedas = Monedas+'.$user2.', '.$updateWins2.' where NombreUsuario="'.$proxturno.'"';
                  $consulta = mysqli_prepare($canal, $sql);

                  if (!$consulta) {
                    echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                    exit;
                  }
              
                  mysqli_stmt_execute($consulta);
                  
                  //Actualizo el campo monedas de la BBDD, que es el que indica que las monedas ya han
                  //sido entregadas en esa partida, y ya no hay que volverlo a hacer
                  $sql = 'update partidas set monedas = 1 where idpartida = '.$idPartida;
                  $consulta = mysqli_prepare($canal, $sql);

                  if (!$consulta) {
                    echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                    exit;
                  }
                  
                  mysqli_stmt_execute($consulta);
                }

                
                //Este if comprobará todas las combinaciones de victoria disponibles para ver si alguno
                //de los jugadores ha ganado
                if (
                  ($combinacionEnviada[0] == $xy && 
                    (
                      ($combinacionEnviada[1] == $xy && $combinacionEnviada[2] == $xy) || 
                      ($combinacionEnviada[4] == $xy && $combinacionEnviada[8] == $xy) || 
                      ($combinacionEnviada[3] == $xy && $combinacionEnviada[6] == $xy)
                    )
                  ) ||
                  ($combinacionEnviada[3] == $xy && 
                    (
                      ($combinacionEnviada[4] == $xy && $combinacionEnviada[5] == $xy)
                    )
                  ) ||
                  ($combinacionEnviada[6] == $xy && 
                    (
                      ($combinacionEnviada[7] == $xy && $combinacionEnviada[8] == $xy) || 
                      ($combinacionEnviada[4] == $xy && $combinacionEnviada[2] == $xy)
                    )
                  ) ||
                  ($combinacionEnviada[1] == $xy && 
                    (
                      ($combinacionEnviada[4] == $xy && $combinacionEnviada[7] == $xy)
                    )
                  ) ||
                  ($combinacionEnviada[2] == $xy && 
                    (
                      ($combinacionEnviada[5] == $xy && $combinacionEnviada[8] == $xy)
                    )
                  )
              ){
                //En caso de que algún jugador ha ganado, se da por hecho que ha sido el último que
                //ha jugado, y se le dará la victoria
                if ($monedas == 0) {
                  monedas(100,0, $nombreUsuario, $proxturno, $canal, $idPartida);
                }

                //Le asigno la victoria el el campo "victoria" de la base de datos
                $sql = 'update partidas set victoria = "'.$nombreUsuario.'" where idpartida = '.$idPartida;
                $consulta = mysqli_prepare($canal, $sql);

                if (!$consulta) {
                  echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                  exit;
                }
                
                mysqli_stmt_execute($consulta);
              }

              //Si no quedan fichas Z (fichas en blanco) y nadie ha ganado la partida, se considerará
              //que la partida ha quedado en empate
              else if ($fichasZ == 0) {
                if ($monedas == 0) {
                  //Llamo a la función monedas con la configuración de empate
                  monedas(50,50, $nombreUsuario, $proxturno, $canal, $idPartida);
                }
                //Actualizo el campo victorias de la BBDD y se la asigno a emp. Como no pueden
                //haber usuarios registrados con nombres de 3 caracteres, nunca chocará.
                $sql = 'update partidas set victoria = "emp" where idpartida = '.$idPartida;
                $consulta = mysqli_prepare($canal, $sql);

                if (!$consulta) {
                  echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                  exit;
                }
                
                mysqli_stmt_execute($consulta);
              } 

              //Finalmente, cambiaré el orden de la partida. Si le tocaba al jugador X,
              //ahora le cambiaré el turno al jugador Y
              if ($xy == "X") {
                $xy = "O";
              }
              else {
                $xy = "X";
              }
              //Actualizaré la BBDD de partidas con la nueva configuración
              $sql = 'update partidas set fichas = "'.$combinacionEnviada.'", turno = "'.$proxturno.'", proxturno = "'.$turno.'", fichaturno = "'.$xy.'" where idpartida = '.$idPartida;
              $consulta = mysqli_prepare($canal, $sql);

              if (!$consulta) {
                echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
                exit;
              }
              
              mysqli_stmt_execute($consulta);

              //Finalmente, devolveré el usuario al tablero para que pueda verlo, aunque no sea
              //su turno.
              header("Location: tablero.php?idpartida=".$idPartida);
              exit;

                
              }
            }
          }
        }
    }
  
}
?>