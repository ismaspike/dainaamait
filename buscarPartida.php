<!--PÁGINA FRONTEND CON UN FORMULARIO PARA CREAR PARTIDA-->
<?php
  //Conexión con los datos de la bbdd
  include '..\..\seguridad\dainaamait\dbaccess.php';

  //Variable que contendrá los datos del usuario en formato HTML
  $sesion = "";

  //Título predeterminado de la página
  $titulo = "Escriba el nombre de usuario de su rival. ";

  //En caso de que haya algún error, este reemplazará al título de la página
  if (isset($_GET['error'])) {
      $titulo .= $_GET['error']; 
  }

  //En caso de que el rival introducido en un principio no sea correcto,
  //nos lo devolverá con get para dejarlo como valor del input
  $rival = "";
  if (isset($_GET['rival'])) {
      $rival = $_GET['rival'];
      $rival = mb_strtolower($rival,'UTF-8');
  }

  //Inicio la sesión, e inicializo la variable que contendrá el link
  //para cerrarla
  session_start();
  $cerrar_sesion = "";
  

  //Compruebo que la sesión está iniciada por alguien, y en caso contrario, voy a la página de error
  //con un mensaje personalizado
  if (!isset($_SESSION["name"])) {
    header('Location: error.php?mensaje=Para buscar partida, tiene que iniciar sesión.');
    exit;
  }
  else {
    //Conecto con los datos del usuario e imprimo sus valores en el cuadro de la derecha
    $nombreUsuario = $_SESSION['name'];
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
    mysqli_stmt_bind_result($consulta, $ganadas, $empatadas, $perdidas, $monedas);
    mysqli_stmt_fetch($consulta);

    $sesion .= "<h3>".$_SESSION['name']."</h3>";
    $sesion .= "<ul><li>Ganadas: ".$ganadas."</li>";
    $sesion .= "<li>Empatadas: ".$empatadas."</li>";
    $sesion .= "<li>Perdidas: ".$perdidas."</li>";
    $sesion .= "<li>Monedas: ".$monedas."</li></ul>";
    $sesion .= '<p style="font-weight: 600; color: blue; font-style: italic;"><a href="./partidas.php">Ver partidas</a></p>';

    
    //Elemento del nav para cerrar sesión
    $cerrar_sesion = '<li class="nav-item">
                        <a class="nav-link" href="cerrar_sesion.php">Cerrar sesion</a>
                      </li>';
    mysqli_stmt_close($consulta);
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
        <script src="./js/equalpassword.js" defer></script>
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
        <a class="nav-link" href="./busqueda.php">Jugar <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./tienda.php">Tienda</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./leaderboard.php">Leaderboard</a>
      </li>
      <!--Este cerrar sesión, en caso de estar vacío, no mostrará nada-->
      <?=$cerrar_sesion?>



    </ul>
</nav>

<div class="grid-container">

  <div class="derecha">
    <div class="rounded-sm inicioSesion" name="inicioSesion">
      <?=$sesion?>
    </div>
  </div>
  
  <div class="izquierda esquina">
  <h1 style="margin-bottom:30px;margin-top:10px;" class="centrar">Juegue una partida</h1>

  <!--FORMULARIO PARA BUSCAR UNA PARTIDA-->
<form id="busquedaPartida" action="busqueda.php" method="post">
  <div class="form-group row">
    <label for="usuarioBusqueda" class="col-sm-10 col-form-label" id="labelBusqueda"><b><?=$titulo?></b></label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="usuarioBusqueda" placeholder="Username" name="usuarioBusqueda" placeholder="Nombre" value="<?=$rival?>">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-10">
      <button type="submit" class="btn btn-primary" id="registerButton">¡Jugar!</button>
    </div>
  </div>
</form>
<div style="color: rgba(0,0,0,.6);">
    <p>*El nombre de usuario tiene que estar previamente registrado</p>
    <p>*Cuando crea una partida nueva, usted empezará y será la ficha de las "x"</p>
</div>
  </div>

  
  
</div>

</body>
</html>