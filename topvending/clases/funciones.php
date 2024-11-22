<?php
declare(strict_types=1);
define("APPROOT","/topvending");
?>

<!-- Funcion de Login -->
<?php
/* Funcion del LOG */
function RegistrarLog($categoría, String $accion): bool
{
    try {
        $logFile = fopen("log.txt", 'a');
        // Escribe una nueva línea en el archivo con los datos proporcionados
        fwrite($logFile, "\n" . date("Y-m-d H:i:s") . $_SESSION['usuario'] . $categoría . $accion);
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

function validar(PDO $conexion, string $user, string $pwd): bool
{
    $result = true;
    $ssql = "SELECT idempleado,user,pass,rol FROM usuarios WHERE user = :userr"; //he quitado el 'and pass' pq hay que usar password verify con la contraseña guardada con el metodo password_hash
    $stmt = $conexion->prepare($ssql);
    $stmt->bindParam(':userr', $user); //quito el bindParam de password pq ya no es necesario
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
   

    
    if ($row === false || $pwd !== $row["pass"]) {
        RegistrarLog(date("r"),"x","error"," No existe en base de datos");
        echo "Usuario o contraseña incorrecto";
        $result = false;
    } else {
            if(estadoEmpleado($conexion,$row["idempleado"])){
            /* SE GUARDA LAS SESSIONS */
            $_SESSION['usuario'] = $row['user'];
            $_SESSION['rol'] = $row['rol']; //he quitado la linea que guardaba la password pq por lo visto da problemas de seguridad
            header("Location:prueba.php");
            $result = true;
            exit;
        } else {
            RegistrarLog(date("r"),$row["idempleado"],"error"," usuario o contraseña incorrecto");
            echo "Usuario o contraseña incorrecto";
            $result = false;
        }
    }

    /* CERRAMOS LA BASE DE DATOS*/
    database::CloseConn();
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
    $user = $_SESSION['usuario'];   
    

    $result = "";
    $rol = $_SESSION["rol"];
    $ssql = "SELECT boton, enlace FROM menu WHERE rol = :rol";
    $stmt = $conexion->prepare($ssql);
    $stmt->bindParam(":rol", $rol);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Inicia la estructura del menú en $result con la etiqueta <header>
    $result .= "<header class='top'>";

    // Recorre cada fila de resultados para agregar botones al menú
    foreach ($rows as $row) {
        // Obtiene el nombre y el enlace del botón de la fila actual
        $nombre = $row['boton'];
        $enlace = APPROOT . $row['enlace'];

        // Añade un botón HTML con la acción de redirigir al enlace especificado
        $result .= "<button onclick='window.location.href=\"$enlace\"'>$nombre</button>";
    }
    // Agrega un botón para cerrar sesión, que redirige a la página de login
    $result .= "<button onclick=\"window.location.href='/topvending/login.php';\">Cerrar Sesión</button>";
    $result .= "<p class='nombre1'>"."Usuario: ".$user. "</p>" ."</header>";
    Database::CloseConn();
    return $result;
}

/* Función para comprobar estado del empleado */
function estadoEmpleado(PDO $conexion, $id): bool
{
    $ssql = "SELECT estadolaboral FROM empleado WHERE idempleado=:id"; //query para sacar de la bd el estado
    $stmt = $conexion->prepare($ssql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT); //pdo param_int para asegurarnos que el valor se pasa correctamente
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return isset($row['estadolaboral']) && $row['estadolaboral'] === 'activo'; // devuelve true o false dependiendo si existe y es igual a activo
}



/*Funcion de prueba que he tenido que hacer para encriptar la password de la base de datos, en teoria las password deben estar encriptadas de base asi que no haria falta
 en el trabajo final*/
 function saludoini(PDO $conexion): string
{
    $user = $_SESSION['usuario'];


    $ssql = "SELECT user,rol FROM usuarios WHERE user = :userr ";
    $stmt = $conexion->prepare($ssql);
    $stmt->bindParam(':userr', $user);

    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $usuario = $row['user'];
    $dpto = $row['rol'];
    $saludo = "<p class='center'>Bienvenido " . $usuario . "  al apartado web de " . $dpto . "</p>";
    return $saludo;
}





?>