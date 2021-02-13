<?php
  include '..\..\seguridad\dainaamait\dbaccess.php';
  $sesion = "";
  session_start();

  $cerrar_sesion = "";
  
  //Compruebo si la sesión está iniciada. En caso de que no, devolveré al usuario
  //a la página de error        
  if (!isset($_SESSION['name'])) {
    header("Location:error.php?mensaje=No puede cambiar de ficha si no ha iniciado sesión. Puede hacerlo en el cuadro de la derecha.");
    exit;
    
  }
  else {
    $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

    if (!$canal) {
        echo "Ha ocurrido el error ".mysqli_connect_errno()." ".mysqli_connect_error()."<br>";
        exit;
    }
    mysqli_set_charset($canal,"utf8");

    //Consulta para comprobar que el nombre de usuario no está
    //en uso
    $sql = "select Ganadas, Empatadas, Perdidas, Monedas, SkinCustom from usuarios where NombreUsuario='".$_SESSION['name']."'";
    
    $consulta = mysqli_prepare($canal, $sql);
   
    
    if (!$consulta) {
        echo "Ha ocurrido el error: ".mysqli_errno($canal)." ".mysqli_error($canal)."<br>";
        exit;
    }
    
    mysqli_stmt_execute($consulta);
    mysqli_stmt_store_result($consulta);
    mysqli_stmt_bind_result($consulta, $ganadas, $empatadas, $perdidas, $monedas, $skinactual);
    mysqli_stmt_fetch($consulta);

    //Compruebo si se ha devuelto vía GET algún mensaje, y lo pongo en la variable $mensaje
    if (isset($_GET['mensaje'])) {
      $mensaje = $_GET['mensaje'];
    }
    //En caso contrario, compruebo cuántas monedas tiene el usuario, ya que las skins
    //valen 400. De ello dependerá el mensaje
    else if ($monedas < 400) {
      $mensaje = "Juegue, consiga más monedas, y podrá comprar cualquier skin a un precio de 400 monedas. ";
    }
    else {
      $mensaje = "¡Compre cualquiera de las skins disponibles a un precio de 400 monedas.";
    }
    
    

    $sesion .= "<h3>".$_SESSION['name']."</h3>";
    $sesion .= "<ul><li>Ganadas: ".$ganadas."</li>";
    $sesion .= "<li>Empatadas: ".$empatadas."</li>";
    $sesion .= "<li>Perdidas: ".$perdidas."</li>";
    $sesion .= "<li>Monedas: ".$monedas."</li></ul>";
    $sesion .= '<p style="font-weight: 600; color: blue; font-style: italic;"><a href="./partidas.php">Ver partidas</a></p>';

    //Abro el directorio que contiene las skins disponibles
    $directorio = opendir("./img/fichas/");
    $contador = 0;
    //Guardo la información de un div que contendrá las skins disponibles para comprar
    $contenido = "<h3>!Bienvenido a la tienda!</h3>";
    $contenido .= '<p style="font-size: 120%;">'.$mensaje.'</p>';

    //Este while recorrerá todas las skins y las guardará en formato HTML en una variable. Lo
    //hará en filas de 4
    while ($elemento = readdir($directorio)) {
        
        if ($elemento != "." && $elemento != ".." && $elemento != "cruz.png" && $elemento != "circ.png") {
            
            if ($contador%5 == 0) {
                $contenido .= '<div class="fila">';
            }
            $contenido .= '<div class="ficha"><img src="./img/fichas/'.$elemento.'" class="logoTienda"><br><form action="comprar.php" method="POST"><input type="hidden" value="'.$elemento.'" name="ficha"><input type="submit" value="¡La quiero!" class="botonSubmit"></form></div>';

            if ($contador%5 == 4) {
                $contenido .= '</div>';
            }
            $contador++;
        }
        
    }

    //Pongo esta línea para poder sacar de algún sitio cuál es la skin actual, y trabajarla con js para ponerle el botón disabled
    echo '<input type="hidden" id="skinactual" value="'.$skinactual.'">';

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
        <link rel="stylesheet" type="text/css" href="./css/tienda.css">
        <script src="./js/header.js" defer></script>
        <script src="./js/tienda.js" defer></script>
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

  <div class="principio">
    
  </div>

  <div class="derecha">
    <div class=" inicioSesion" name="inicioSesion">
      <?=$sesion?>
    </div>
  </div>
  
  <div class="izquierda esquina">
    <div id="main"><?=$contenido?><br>
      
  </div>

  
  
</div>

</body>
</html>

<?php 
  //En caso de que el usuario tenga menos de 400 monedas, todos los botones de compra
  //permanecerán deshabilitados
  if ($monedas < 400) {
  echo '<script type="text/javascript" defer> 
  let botones = document.getElementsByClassName("botonSubmit");
  console.log(botones.length);
  for (let a = 0; a < botones.length; a++) {
    botones[a].disabled = "disabled";
  }
  
</script>';
  
}
?>