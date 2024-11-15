<?php

declare(strict_types=1);
?>

<!-- Funcion de Login -->
<?php
/* Funcion del LOG */
function RegistrarLog($timestamp, $idusuario, $categoría, String $accion): bool
{
    try {
        $logFile = fopen("log.txt", 'a');
        // Escribe una nueva línea en el archivo con los datos proporcionados
        fwrite($logFile, "\n" . $timestamp . $idusuario . $categoría . $accion);
        fclose($logFile);

        // Retorna true si la operación fue exitosa
        return true;
    } catch (Exception $e) {
        error_log($e->getMessage());
        // Retorna false si la operación falló
        return false;
    }
}

/* conectarse a la base de datos */

function conectar(): bool|PDO
{
    $dbh = Database::getInstance();
    if (isset($dbh)) {
        // Si existe, retorna la conexión (objeto PDO)
        return $dbh;
    } else {
        // Si no existe, retorna false indicando fallo en la conexión
        return false;
    }
}

/* FUNCION DEL LOGIN */
function login(): bool
{
    // Inicializa la variable $result en false para indicar que el login no ha sido exitoso
    $result = false;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Comprueba que los campos 'usuario' y 'contraseña' 
        if (isset($_POST["usuario"]) && isset($_POST["contraseña"])) {
            $user = $_POST["usuario"];
            $pwd = $_POST["contraseña"];

            try {
                $dbh = Database::getInstance();
                // Llama a la función validar para verificar las credenciales del usuario
                $result = validar($dbh, $user, $pwd);
            } catch (PDOException $e) {
                // En caso de error en la base de datos, muestra el mensaje y establece $result en false
                echo "Error: " . $e->getMessage();
                $result = false;
            }
        }
    }
    return $result;
}


/* FUNCION PARA VALIDAR LOGIN */
function validar(PDO $conexion, string $user, string $pwd): bool
{
    // Inicializa $result en true para indicar éxito por defecto (se cambiará si falla)
    $result = true;
    $ssql = "SELECT user, pass, rol FROM usuarios WHERE user = :userr AND pass = :passs";
    $stmt = $conexion->prepare($ssql);
    $stmt->bindParam(':userr', $user);
    $stmt->bindParam(':passs', $pwd);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        // Si no hay coincidencia, muestra un mensaje de error y cambia $result a false
        echo "Usuario o contraseña incorrecto";
        $result = false;
    } else {
        // Si el usuario es válido, guarda los datos en la sesión
        $_SESSION['usuario'] = $row['user'];
        $_SESSION['rol'] = $row['rol'];
        $_SESSION['pass'] = $row['pass'];
        header("Location:./modules/Modulo_Fabricacion/fabricacion.php");
        $result = true;
        exit;
    }
    Database::CloseConn();
    // Retorna el resultado (true si se autenticó correctamente, false si no)
    return $result;
}

/* Funcion para cerrar la sesion */
function cerrarSesion()
{
    session_unset();
    session_destroy();
}

/* Comprueba si hay sesion */
function hay_sesion(): bool
{
    // Verifica si existe una sesión activa comprobando si 'usuario' está definida en $_SESSION
    return isset($_SESSION['usuario']);
}


/* Si no hay sesion redirige al login */
function redirect()
{
    // Verifica si no hay una sesión activa; si no la hay, redirige al usuario a la página de login
    if (!hay_sesion()) {
        header('Location: ./login.php');
        exit; // Detiene la ejecución del script después de la redirección
    }
}


/* FUNCION DE LOS BOTONES */

function crearMenu(PDO $conexion): string
{
    $conexion = Database::getInstance();
    $result = "";
    $rol = $_SESSION["rol"];
    $ssql = "SELECT boton, enlace FROM menu WHERE rol = :rol";
    $stmt = $conexion->prepare($ssql);
    $stmt->bindParam(":rol", $rol);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicia la estructura del menú en $result con la etiqueta <header>
    $result .= "<header>";

    // Recorre cada fila de resultados para agregar botones al menú
    foreach ($rows as $row) {
        // Obtiene el nombre y el enlace del botón de la fila actual
        $nombre = $row['boton'];
        $enlace = "./../../modules/m0/" . $row['enlace'];

        // Añade un botón HTML con la acción de redirigir al enlace especificado
        $result .= "<button onclick='window.location.href=\"$enlace\"'>$nombre</button>";
    }
    // Agrega un botón para cerrar sesión, que redirige a la página de login
    $result .= "<button onclick=\"window.location.href='../../login.php';\">Cerrar Sesión</button>";
    $result .= "</header>";
    Database::CloseConn();
    return $result;
}


?>