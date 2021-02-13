<?php
  //Inicio la sesión
  include '..\..\seguridad\dainaamait\dbaccess.php';
  $sesion = "";
  session_start();
  $cerrar_sesion = "";
  $disabled = "";
  $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

  //Compruebo si la sesión está iniciada
  if (!isset($_SESSION['name'])) {
    echo '<script src="./js/sessionbox.js" defer></script>';
    $sessionuser = "";
    if (isset($_GET['sessionuser'])) {
      $sessionuser = $_GET['sessionuser'];
    }
    $sesion = '<p style="font-size: 110%">Inicie sesión</p>
    <form action="iniciar.php" method="post">
  <div class="form-group row">
    <div class="col-sm-10">
      <input type="text" class="form-control" id="sessionuser" placeholder="Nombre de usuario" name="sessionuser" value="'.$sessionuser.'">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-10">
      <input type="password" class="form-control" id="sessionpass" placeholder="Contraseña" name="sessionpass">
    </div>
  </div>

  <div class="form-group row">
    <div class="col-sm-10">
      <input type="submit" class="btn btn-primary" value="Iniciar sesión">
    </div>
  </div>
</form>
    <p><a href="registro.php">Registrarse</a></p>
';
    $disabled = 'disabled';
  }
  else {
    $nombreUsuario = $_SESSION['name'];

    if (!$canal) {
        echo "Ha ocurrido el error ".mysqli_connect_errno()." ".mysqli_connect_error()."<br>";
        exit;
    }

    //Conecto con la BBDD para imprimir el cuadro de la derecha
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
  }

  //Ahora, conecto con la BBDD para sacar la información de los
  //10 jugadores con más victorias
  $sql = 'select NombreUsuario, Ganadas, Empatadas, Perdidas from usuarios ORDER BY Ganadas DESC limit 10';
    
  $consulta = mysqli_prepare($canal,$sql);
  if (!$consulta) {
    echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal);
    exit;
  }
  
  //Le indico que quiero los resultados en variables
  mysqli_stmt_execute($consulta);
  mysqli_stmt_bind_result($consulta, $nombre, $ganadas, $empatadas, $perdidas);
?>

<html>
    <head>
        <title>Daina Amait</title>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="./css/header.css">
        <link rel="stylesheet" type="text/css" href="./css/nav.css">
        <link rel="stylesheet" type="text/css" href="./css/comun.css">
        <link rel="stylesheet" type="text/css" href="./css/leaderBoard.css">
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
        <a class="nav-link <?=$disabled?>" href="./buscarPartida.php">Jugar</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?=$disabled?>" href="./tienda.php">Tienda</a>
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
    <h3>LEADERBOARD: </h3>

    <div class="partidas">
      <?php
      //Una vez dentro del HTML, imprimo una tabla con los 10 resultados obtenidos previamente de la BBDD
      echo '<table border="3"><tr><th>Posición</th><th>Nombre</th><th>Ganadas</th><th>Empatadas</th><th>Perdidas</th></tr>';
      $contador = 1;
        while (mysqli_stmt_fetch($consulta)) {
          echo '<tr class="leaderColumn">';
          echo '<td class="posicion">'.$contador.'</td>';
          echo '<td class="nombre">'.$nombre.'</td>';
          echo '<td class="result">'.$ganadas.'</td>';
          echo '<td class="result">'.$empatadas.'</td>';
          echo '<td class="result">'.$perdidas.'</td>';
          echo "</tr>";
          $contador++;
        }
      ?>
    </div>
  
</div>


</body>
</html>
