<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        #usuario {
            text-align: center;
        }

        #contraseña {
            text-align: center;
        }
    </style>
    <link rel="stylesheet" href="../../css/login.css">
</head>

<body>

    <img src="../../css/login.webp" class="imagen">

    <div class="dev">

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <h2 class="titu">Inicio de Sesión</h2>
            <input type="text" id="usuario" name="usuario" placeholder="Usuario">
            <input type="password" id="contraseña" name="contraseña" placeholder="Contraseña">
            <button type="submit" class="sesion">Iniciar Sesion</button>
        </form>
    </div>
    <?php
    //incluye la pagina donde se hace el login
    require_once("./lib/funciones.php");
    //incluye la conexion a la base de datos
    require_once("./lib/basededatos.php");
    //funcion de login 
    login();
    ?>

</body>

</html>