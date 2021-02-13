<?php
//Esta es la página de error. Si algo va mal con el funcionamiento de la
//página, entrará aquí con un mensaje de error personalizado

  include '..\..\seguridad\dainaamait\dbaccess.php';

  //Creación de variables
  $sesion = "";
  $disabled = "";

  //Inicio la sesión
  session_start();

  //Aquí compruebo si hay un mensaje de error vía GET. En caso contrario, 
  //pondré el mensaje predeterminado.
  $mensaje = 'Ha ocurrido un error inesperado.<br>Pulse <a href="index.php">aquí</a> para volver al menú principal.';
  if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
  }

  //Compruebo si está iniciada la sesión, para mostrar el cuadro de la derecha
  //como cuadro de inicio de sesión o no
  if (!isset($_SESSION['name'])) {
    echo '<script src="./js/sessionbox.js" defer></script>';
    $sessionuser = "";
    if (isset($_GET['sessionuser'])) {
      $sessionuser = $_GET['sessionuser'];
    }
    $sesion = '<p style="font-size: 110%">Inicie sesión</p>
    <form action="iniciar.php?redirect_to=index.php" method="post">
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
$disabled = "disabled";
  }
  else {
    //Si se ha iniciado sesión, conecto con la base de datos
    $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

    if (!$canal) {
        echo "Ha ocurrido el error ".mysqli_connect_errno()." ".mysqli_connect_error()."<br>";
        exit;
    }
    mysqli_set_charset($canal,"utf8");

    //Saco de la base de datos la información del usuario
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

    //Meto en la variable sesion el cuadrado de la derecha con toda
    //la información del usuario
    $sesion .= "<h3>".$_SESSION['name']."</h3>";
    $sesion .= "<ul><li>Ganadas: ".$ganadas."</li>";
    $sesion .= "<li>Empatadas: ".$empatadas."</li>";
    $sesion .= "<li>Perdidas: ".$perdidas."</li>";
    $sesion .= "<li>Monedas: ".$monedas."</li></ul>";
    $sesion .= '<p style="font-weight: 600; color: blue; font-style: italic;"><a href="./partidas.php">Ver partidas</a></p>';

    $cerrar_sesion = '<li class="nav-item">
                        <a class="nav-link" href="cerrar_sesion.php">Cerrar sesion</a>
                      </li>';
  }
?>
<html>
    <head>
        <title>Daina Amait</title>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="./css/header.css">
        <link rel="stylesheet" type="text/css" href="./css/nav.css">
        <link rel="stylesheet" type="text/css" href="./css/comun.css">
        <link rel="stylesheet" type="text/css" href="./css/index.css">
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
  <h1 style="text-align: center;">Bienvenido a Dainamait, </h1><br>
      <p style="font-size:140%;"><?=$mensaje?><br>Puede volver al menú principal haciendo clic <a href="./index.php">aquí</a>.</p><br>
      
  </div>

  
  
</div>

</body>
</html>