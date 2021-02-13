<?php
  //ESTA PÁGINA, SI TODO ES CORRECTO, IMPRIMIRÁ EL TABLERO DE LA PARTIDA
  //QUE SE LE INDIQUE

  include '..\..\seguridad\dainaamait\dbaccess.php';
  session_start();

  //Creación de variables
  $sesion = "";
  $partida = "";
  $tablero = "";
  $idpartida;
  $cerrar_sesion = "";

  //Compruebo si la sesión está iniciada. Si no lo está, devuelvo al usuario
  //a la página de error
  if (!isset($_SESSION['name'])) {
    header('Location: error.php?mensaje=No puede acceder al tablero sin haber iniciado sesión.');
    exit;
  }

  else {
    //Conecto con la base de datos
    $nombreUsuario = $_SESSION['name'];
    $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

    if (!$canal) {
        echo "Ha ocurrido el error ".mysqli_connect_errno()." ".mysqli_connect_error()."<br>";
        exit;
    }
    mysqli_set_charset($canal,"utf8");

    //Hago una consulta para sacar los datos que van impresos en el 
    //cuadro de la derecha
    $sql = "select Ganadas, Empatadas, Perdidas, Monedas from usuarios where NombreUsuario='".$_SESSION['name']."'";
    
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

    

    $cerrar_sesion = '<li class="nav-item">
                        <a class="nav-link" href="cerrar_sesion.php">Cerrar sesion</a>
                      </li>';
    mysqli_stmt_close($consulta);

    //Compruebo si se ha accedido al tablero con alguna partida. Si no se ha hecho
    //no se podrá jugar, ya que no hay información de ninguna partida
    if (isset($_GET['idpartida'])) {
      $idpartida = $_GET['idpartida'];

      //Consulta que sacará toda la información acerca de los dos jugadores de la partida,
      //sus skins, de quién es el turno, y la combinación que hay en el tablero
      $sql = 'select fichas, logox, logoy, jugadorx, jugadory, turno, proxturno, fichaturno, victoria from partidas where idpartida = "'.$idpartida.'" and (jugadorx = "'.$nombreUsuario.'" or jugadory = "'.$nombreUsuario.'")';
      $consulta = mysqli_prepare($canal, $sql);
      if (!$consulta) {
        echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
        exit;
      }
      mysqli_stmt_execute($consulta);
      mysqli_stmt_store_result($consulta);
      mysqli_stmt_bind_result($consulta, $fichas, $logox, $logoy, $jugadorx, $jugadory, $turno, $proxturno, $fichaturno, $victoria);
      mysqli_stmt_fetch($consulta);
      $encontrados = mysqli_stmt_num_rows($consulta);

      //Aquí compruebo que la partida que se ha buscado existe. Si no es así, 
      if ($encontrados == 0) {
        mysqli_stmt_close($consulta);
        header("Location: error.php?mensaje=La partida solicitada no existe.");
      }

      else {

        //Aquí comprobaré si la partida ha finalizado o no
        $mensajeTurno;
        //En caso de que el nombre de usuario coincida con el registro de
        //"victoria" de la BBDD, será porque el usuario ha ganado la partida
        if (strcasecmp($nombreUsuario,$victoria) == 0) {
          $mensajeTurno = "¡Enhorabuena! Ha ganado la partida.";
        }
        //Si en el campo "victoria" pone "emp", la partida quedará como empate.
        //Podría pensarse que también puede haber un usuario que se llama "emp", pero
        //esto no puede ocurrir porque el nombre de usuario tiene que tener al menos
        //5 caracteres
        else if ($victoria == "emp") {
          $mensajeTurno = "¡Ha sido un empate!.";
        }
        //Si "victoria" es "null" es porque todavía no se ha modificado, por tanto
        //la partida sigue activa
        else if ($victoria != null) {
          $mensajeTurno = "Su contrincante ha ganado la partida.";
        }
        //Como la partida sigue activa, voy a comprobar de quién es el turno. El registro
        //"turno" de la BBDD contiene el nombre del usuario del usuario que es su turno
        //Al usuario que sea su turno se le permitirá poner una ficha (esto lo condiciona)
        //el script turno.js, pero aunque no sea el turno de ese usuario y haga trampas
        //para jugar, el módulo de "enviarPartida.php" lo detectará y dará error
        else if (strcasecmp($nombreUsuario, $turno) == 0) {
          $mensajeTurno = "Es su turno";
          echo '<script src="./js/turno.js" defer></script>';
          //Guardo la ficha que tiene el usuario y el id de la partida en input ocultos
          //de cara a que sea fácil rescatarlos después. Si el usuario modificara estos,
          //el módulo de "enviarPartida.php" lo detectaría y daría error
          echo '<input type="hidden" value="'.$idpartida.'" name="idPartida" id="idPartida">';
          echo '<input type="hidden" value="'.$fichaturno.'" id="fichaTurno">';
          
          if ($fichaturno == "X") {
            echo '<input type="hidden" value="'.$logox.'" id="logoActual">';
          }
          else {
            echo '<input type="hidden" value="'.$logoy.'" id="logoActual">';
          }
        }

        else {
          //En caso de que sea el turno del rival, la página se refrescará cada 5 segundos para
          //que se vaya comprobando si el oponente ha hecho su jugada
          $mensajeTurno = "Es el turno de su contrincante";
          header('Refresh: 5');
        }

        //Este es el mensaje de arriba, indicando qué jugador es cada ficha
        $partida = "Jugador X: ".$jugadorx." | Jugador Y: ".$jugadory." -> <i>".$mensajeTurno."</i>";

        //Esta es la estructura del tablero y del menú de la izquierda
        $tablero = '<div style="width: 40%; display: inline-block;">
       
        <h4 id="fichasDeX">Fichas de '.$jugadorx.'</h4>
                    <img src="./img/fichas/'.$logox.'" class="X logo">
                    <hr>
                    <h4 id="fichasDeY">Fichas de '.$jugadory.'</h4>
                    <img src="./img/fichas/'.$logoy.'" class="O logo"><p style="font-size: 1px;">z</p><hr></div>';
        $tablero .= '<div style="width: 50%; display: inline-block;">';
        $tablero .= '<table border="1">';

        for ($a = 0; $a < 9; $a++) {
          if ($a%3 == 0) {
            $tablero .= '<tr>';
          }
          $tablero .= '<td class="ficha"><img class="logo"></td>';
          if ($a%3 == 2) {
            $tablero .= "</tr>";
          }
        }
        $tablero .='</table></div><div id="turno" style="display: block;"></div>
        <input type="hidden" value="'.$fichaturno.'" id="fichaTurno">';
      }
    }
    else {
      $partida = "Ha entrado al tablero sin tener una partida activa.";
    }
}
?>
<html>
    <head>
        <title>Daina Amait</title>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="./css/header.css">
        <link rel="stylesheet" type="text/css" href="./css/nav.css">
        <link rel="stylesheet" type="text/css" href="./css/comun.css">
        <link rel="stylesheet" type="text/css" href="./css/tablero.css">
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
        <a class="nav-link" href="./leaderboard.php">Leaderboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./tienda.php">Tienda</a>
      </li>

      <?=$cerrar_sesion?>



    </ul>
</nav>

<div class="grid-container">

  <div class="principio">
    
  </div>

  <div class="derecha">
    <div class=" inicioSesion" name="inicioSesion">
      <?=$sesion?>
    </div>
  </div>
  
  <div class="izquierda esquina">
  <form action="enviarPartida.php" method="POST" id="formularioTablero">
    <h3 style="text-align: left;"><?=$partida?></h3><br>
  
    <p><?=$tablero?></p><br>
  </form>
      
  </div>
  
</div>


</body>
</html>

<script>
    //Con este script pondré a cada ficha del tablero la imagen que le corresponda

      let tds = document.getElementsByClassName("ficha");
      console.log(tds.length);
      for (let a = 0; a < tds.length; a++) {
        console.log("<?php echo $fichas?>"[a]);
        tds[a].classList.add("<?php echo $fichas?>"[a]);
        let imagen = tds[a].getElementsByTagName("img");
        if ("<?php echo $fichas?>"[a] == "X") {
            imagen[0].src = "./img/fichas/<?php echo $logox?>";
            console.log("Es una x")
        }
        else if ("<?php echo $fichas?>"[a] == "O") {
            imagen[0].src = "./img/fichas/<?php echo $logoy?>";
            console.log("Es una O")
        }
        else {
          imagen[0].src = "./img/fichas/nada.png";
          console.log("Es una Z")
        }

    }
  </script>