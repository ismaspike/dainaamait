<?php
  //Incluyo los archivos de la base de datos
  include '..\..\seguridad\dainaamait\dbaccess.php';

  //Inicio la variable que contendrá, bien el formulario de inicio
  //de sesión, o bien los datos del usuario (si ya ha iniciado)
  $sesion = "";

  //Inicio la variable que contendrá el link para cerrar sesión.
  //Solamente le meteré contenido en caso de que la sesión haya
  //sido iniciada
  $cerrar_sesion = "";
  $disabled = "";

  //Inicio la sesión PHP
  session_start();

  //Este es el mensaje predeterminado de la página index
  $mensaje = "Juegue online contra otros usuarios, consiga puntos, y compre sus propias skins.
    ¡También puede subir posiciones para que su nombre sea mostrado en la leaderscore!";
  
  

  //En caso de que otro $mensaje sea pasado por método get, lo reemplazaré
  if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
  }

  //Este if meterá en $sesion el formulario de inicio de sesión, en caso de que no esté iniciada
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
    $disabled = 'disabled';
  }
  else {
    //En caso de que la sesión esté iniciada, buscaré los datos del usuario en cuestión
    //en la base de datos
    $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);
    if (!$canal) {
        echo "Ha ocurrido el error ".mysqli_connect_errno()." ".mysqli_connect_error()."<br>";
        exit;
    }
    mysqli_set_charset($canal,"utf8");

    $sql = "select Ganadas, Empatadas, Perdidas, Monedas from usuarios where NombreUsuario='".$_SESSION['name']."'";
    $consulta = mysqli_prepare($canal, $sql);

    if (!$consulta) {
        echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
        exit;
    }
    
    mysqli_stmt_execute($consulta);
    mysqli_stmt_store_result($consulta);
    //Los resultados de la búsqueda en la base de datos, los meto en variables
    mysqli_stmt_bind_result($consulta, $ganadas, $empatadas, $perdidas, $monedas);
    mysqli_stmt_fetch($consulta);

    //Meto en $sesion los datos del usuario (monedas, resultados de partidas, etc...)
    $sesion .= "<h3>".$_SESSION['name']."</h3>";
    $sesion .= "<ul><li>Ganadas: ".$ganadas."</li>";
    $sesion .= "<li>Empatadas: ".$empatadas."</li>";
    $sesion .= "<li>Perdidas: ".$perdidas."</li>";
    $sesion .= "<li>Monedas: ".$monedas."</li></ul>";
    $sesion .= '<p style="font-weight: 600; color: blue; font-style: italic;"><a href="./partidas.php">Ver partidas</a></p>';

    //Como el usuario tiene sesión iniciada, cambio el mensaje predeterminado
    //que se le mostrará en la página
    $mensaje = "Bienvenido a Daina Amait, ".$_SESSION['name'].".<br>¡Pulse aquí para empezar a jugar!";

    //Meto en $cerrar_sesion el link para cerrar sesion, que será impreso en el nav
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
        <a href="#" class="logo"><img src="./img/logo.png" height="20%" id="imagenLogo"></a>
     </div>
    </header>

    <nav class="navbar navbar-expand-lg navbar-light azulverde">
   
  <a class="navbar-brand" href="#">DainaAmait</a>
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


      <!--Este cerrar sesión, en caso de estar vacío, no mostrará nada-->
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
  <h1 style="text-align: center;">Bienvenido a Dainamait, </h1><br>
  <p style="font-size:150%;">Dainamait es el concepto es videojuego online que todos estaban deseando.</p>
      <p style="font-size:140%;"><?=$mensaje?></p><br>
      
  </div>

  
  
</div>

</body>
</html>