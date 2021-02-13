<?php
  include '..\..\seguridad\dainaamait\dbaccess.php';
  $sesion = "ufhdsui";
  $disabled = "";
  session_start();
  $error = "";

  //Compruebo si se ha devuelto algún mensaje de error
  if (isset($_GET['error'])) {
    $error = $_GET['error'];
  }

  //Compruebo si se ha devuelto la variable de usuario
  $usuario = "";
  if (isset($_GET['usuario'])) {
    $usuario = $_GET['usuario'];
  }

  //Compruebo si la sesión estaba iniciada previamente
  if (isset($_SESSION['name'])) {
    header('Location: error.php?mensaje=Ya está registrado.');
    exit;
  }

  //En caso de que la sesión no estuviera iniciada previamente,
  //continuamos con el procedimiento
  else {
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
';
$disabled = "disabled";

//La variable $register contendrá el cuadro de registro de usuario
$register = '<h1 style="margin-bottom:30px;margin-top:10px;" class="centrar">Regístrese</h1>
<form id="formularioRegistro" action="registrar.php" method="post">
  <div class="form-group row">
    <label for="registeruser" class="col-sm-10 col-form-label" id="labeluser"><b>Username:</b></label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="registeruser" placeholder="Username" name="registeruser" value="'.$usuario.'">
    </div>
  </div>
  <div class="form-group row">
    <label for="registerpass" class="col-sm-10 col-form-label" id="labelpass"><b>Contraseña</b></label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="registerpass" placeholder="Contraseña" name="registerpass">
    </div>
  </div>
  <div class="form-group row">
    <label for="registerpass2" class="col-sm-10 col-form-label" id="labelpass2"><b>Confirme su contraseña</b></label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="registerpass2" placeholder="Confirme su contraseña" name="registerpass2">
    </div>
  </div>
  <div class="form-group row">

    <div class="col-sm-10">
      <div class="form-check">
    <label class="form-check-label" id="labelterminos">
      <input class="form-check-input" type="checkbox" id="terminos" name="terminos"> Acepto los términos y condiciones de Dainaamait
    </label>
      </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-10">
      <button type="submit" class="btn btn-primary" id="registerButton">Registrarse</button>
    </div>
  </div>
</form>';

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

  <div class="derecha">
    <div class="rounded-sm inicioSesion" name="inicioSesion">
      <?=$sesion?>
    </div>
  </div>
  
  <div class="izquierda esquina">
    <?=$error?>
    <?=$register?>
  </div>

  
  
</div>

</body>
</html>