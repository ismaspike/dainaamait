<!--PÁGINA BACKEND PARA COMPRAR SKINS (FICHAS)-->
<?php
  include '..\..\seguridad\dainaamait\dbaccess.php';

  session_start();

  //Compruebo que está iniciada la sesión, sino, devolveré al usuario a la página
  //de error con un mensaje personalizado
  if (!isset($_SESSION['name'])) {
    header("Location:error.php?mensaje=No puede comprar fichas si no ha iniciado sesión.");
    exit;
    
  }
  else {
      //Compruebo que el usuario ha entrado aquí pulsando la ficha que quiere comprar. En 
      //caso contrario, le mandaré a la página de error.
      if (!isset($_POST['ficha'])) {
        header("Location: error.php?mensaje=Ha ocurrido un error con la tienda. Inténtelo de nuevo más tarde");
        exit;
      }
      else {
        $fichaComprada = $_POST['ficha'];
        $canal = @mysqli_connect(IP,USUARIO,CLAVE,BD);

        if (!$canal) {
            echo "Ha ocurrido el error ".mysqli_connect_errno()." ".mysqli_connect_error()."<br>";
            exit;
        }
        mysqli_set_charset($canal,"utf8");

        //Compruebo que, pese a que en la tienda le he bloqueado su skin actual, no
        //ha intentado comprar la misma
        $sql = "select SkinCustom from usuarios where NombreUsuario='".$_SESSION['name']."'";
        
        $consulta = mysqli_prepare($canal, $sql);

        mysqli_stmt_execute($consulta);
        mysqli_stmt_store_result($consulta);
        mysqli_stmt_bind_result($consulta, $skinactual);
        mysqli_stmt_fetch($consulta);
        
        //En caso de que haya clicado en la misma ficha, le mando a la página anterior
        //con un mensaje de error indicándolo
        if ($skinactual == $fichaComprada) {
          header("Location:tienda.php?mensaje=No seleccione su ficha. ¿Para qué le deshabilitamos el botón?");
          exit;
        }
        else {
          //En caso de que haya elegido cualquier otra ficha, compruebo que existe y
          //que no le ha cambiado el nombre para liar al programa
          if (!file_exists("./img/fichas/".$fichaComprada)) {
            header("Location: error.php?mensaje=Ha ocurrido un error en la compra. Inténtelo de nuevo más tarde.");
            exit;
          }
          
          mysqli_stmt_close($consulta);

          //Ahora comprobaré cuántas monedas tiene el usuario. Hacen falta 400 para comprar una ficha
          $sql = 'select Monedas from usuarios where NombreUsuario ="'.$_SESSION['name'].'"';
          $consulta = mysqli_prepare($canal, $sql);

          mysqli_stmt_execute($consulta);
          mysqli_stmt_store_result($consulta);
          mysqli_stmt_bind_result($consulta, $monedas);
          mysqli_stmt_fetch($consulta);

          //En caso de que el usuario tenga menos de 400 monedas e intente comprar ficha, pese a que
          //si no las tiene le bloqueo los botones, Le mando a la página de la tienda con un mensaje de error.
          if ($monedas <= 400) {
            header("Location: tienda.php?mensaje=No puede comprar si no tiene monedas. ¿Para qué le deshabilitamos los botones?");
            exit;
          }
          else {
            mysqli_stmt_close($consulta);

            //Finalmente, si todo ha ido bien, procedemos con la compra de la ficha.
            $sql = 'update usuarios set Monedas = Monedas-400, SkinCustom="'.$fichaComprada.'" where NombreUsuario = "'.$_SESSION['name'].'"';
            $consulta = mysqli_prepare($canal, $sql);

            mysqli_stmt_execute($consulta);
            header("Location: tienda.php?mensaje=Ha comprado la ficha satisfactoriamente.");
          }

          
        }

    }
  }
?>
