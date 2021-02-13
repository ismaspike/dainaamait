<?php

  include '..\..\seguridad\dainaamait\dbaccess.php';
  $sesion = "";
  $sesion2 = "";
  session_start();
  $cerrar_sesion = "";

  //Compruebo si la sesión está iniciada. En caso contrario, mando
  //al usuario a la página de error, ya que no debería de estar aquí
  if (!isset($_SESSION['name'])) {
    header('Location: error.php?mensaje=No puede ver sus partidas si no ha iniciado sesión.');
    exit;
  }
  else {
    //Conecto con la BBDD
    $nombreUsuario = $_SESSION['name'];
    $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

    if (!$canal) {
        echo "Ha ocurrido el error ".mysqli_connect_errno()." ".mysqli_connect_error()."<br>";
        exit;
    }
    mysqli_set_charset($canal,"utf8");

    $sql = 'select Ganadas, Empatadas, Perdidas, Monedas from usuarios where NombreUsuario="'.$_SESSION['name'].'"';
    
    $consulta = mysqli_prepare($canal, $sql);
  
    
    if (!$consulta) {
        echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
        exit;
    }
    
    mysqli_stmt_execute($consulta);
    mysqli_stmt_store_result($consulta);
    mysqli_stmt_bind_result($consulta, $ganadas, $empatadas, $perdidas, $monedas);
    mysqli_stmt_fetch($consulta);

    //Imprimo el cuadro de la derecha
    $sesion .= "<h3>".$_SESSION['name']."</h3>";
    $sesion .= "<ul><li>Ganadas: ".$ganadas."</li>";
    $sesion .= "<li>Empatadas: ".$empatadas."</li>";
    $sesion .= "<li>Perdidas: ".$perdidas."</li>";
    $sesion .= "<li>Monedas: ".$monedas."</li></ul>";
    $sesion .= '<p style="font-weight: 600; color: blue; font-style: italic;"><a href="./partidas.php">Ver partidas</a></p>';

    //Imprimo el botoncico de cerrar sesión
    $cerrar_sesion = '<li class="nav-item">
                        <a class="nav-link" href="cerrar_sesion.php">Cerrar sesion</a>
                      </li>';
    mysqli_stmt_close($consulta);

    //Pido a la base de datos todas las partidas en las que el usuario forma parte
    $sql = 'select idpartida, turno, proxturno, monedas from partidas where jugadorx = "'.$nombreUsuario.'" OR jugadory = "'.$nombreUsuario.'"';
    
    $consulta = mysqli_prepare($canal,$sql);
    if (!$consulta) {
      echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal);
      exit;
    }
    
    //Guardo los resultados de la consulta en variables
    mysqli_stmt_execute($consulta);
    mysqli_stmt_store_result($consulta);
    mysqli_stmt_bind_result($consulta, $idpartida, $turno, $proxturno, $monedas);
  }
?>

<html>
    <head>
        <title>Daina Amait</title>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="./css/header.css">
        <link rel="stylesheet" type="text/css" href="./css/nav.css">
        <link rel="stylesheet" type="text/css" href="./css/comun.css">
        <link rel="stylesheet" type="text/css" href="./css/partidas.css">
        <script src="./js/header.js" defer></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
  
    </head>

    <body>
    <header>
      <div class="header verdeazul">
        <a href="./index.php" class="logo"><img src="./img/logo.png" height="20%" id="imagenLogo"></a>
     </div>
    </header>

    <nav class="navbar navbar-expand-lg navbar-light azulverde">
   
  <a class="navbar-brand" href="./index.php">DainaAmait</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="./buscarPartida.php">Jugar</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./tienda.php">Tienda</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./leaderboard.php">Leaderboard</a>
      </li>

      <?=$cerrar_sesion?>



    </ul>
</nav>

<div class="grid-container">

  <div class="derecha">
    <div class=" inicioSesion" name="inicioSesion">
      <?=$sesion?>
    </div>
  </div>
  
  <div class="izquierda esquina">
    <h3>Estas son sus partidas: </h3>

    <div class="partidas">
      <?php

        mysqli_stmt_store_result($consulta);

        //Asigno a $encontrados el número de registros que ha devuelto
        //la consulta
        $encontrados = mysqli_stmt_num_rows($consulta);

        //Si el número de registros de la consulta es 0, sacaré un mensaje 
        //diferente indicando que el usuario todavía no tiene partidas
        if ($encontrados == 0){
          echo '<h6>Aún no tiene ninguna partida. Puede empezar una haciendo clic <a href="./buscarPartida.php">aquí</a>.</h6>';
        }

        //En caso contrario, imprimo todas sus partidas
        else {
          while (mysqli_stmt_fetch($consulta)) {
            echo '<div class="partida">';
            $miTurno;
            $fraseTurno;
            if ($turno == $nombreUsuario) {
              $rival = $proxturno;
              $miTurno = true;
              $fraseTurno = "Es su turno";
            }
            else {
              $rival = $turno;
              $miTurno = false;
              $fraseTurno = "Es el turno de ".$rival;
            }
            if ($monedas == 1) {
              $fraseTurno = "La partida ha finalizado";
            }
              echo '<h5 class="rival">Partida contra: <i>'.$rival.'</i></h5>';
              echo '<p><a href="./tablero.php?idpartida='.$idpartida.'">'.$fraseTurno.'</a></p>';
          echo '</div>';
          }
        }
      ?>
    </div>
  
</div>


</body>
</html>
