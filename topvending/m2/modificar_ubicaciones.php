<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();
echo crearMenu($dbh);

// Recuperar las cookies individuales si están definidas
$idubicacion = isset($_COOKIE['idubicacion']) ? htmlspecialchars($_COOKIE['idubicacion']) : '';
$cliente = isset($_COOKIE['cliente']) ? htmlspecialchars($_COOKIE['cliente']) : '';
$dir = isset($_COOKIE['dir']) ? htmlspecialchars($_COOKIE['dir']) : '';
$direccion = explode(";", $dir); // Separo la cadena completa de dirección en un array
// Asigno cada posición del array a una variable
$calle = $direccion[0];
$num_portal = $direccion[1];
$cod_postal = $direccion[2];
$provincia = $direccion[3];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar si el parámetro idubicacion está presente en la URL
        if (isset($_POST['idubicacion'])) {
            $idubicacion = $_POST['idubicacion'];
    
            if(isset($_POST['BorrarMaquina'])){
                // Eliminar los registros dependientes de maquinaproducto
                $sql_maquinaproducto = "DELETE FROM maquinaproducto WHERE idmaquina IN (SELECT idmaquina FROM maquina WHERE idubicacion = :idubicacion)";
                $stmt_maquinaproducto = $dbh->prepare($sql_maquinaproducto);
                $stmt_maquinaproducto->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                $stmt_maquinaproducto->execute();

                // Eliminar primero las máquinas asociadas
                $sql_maquinas = "UPDATE maquina SET idubicacion = 1 WHERE idubicacion = :idubicacion";
                $stmt_maquinas = $dbh->prepare($sql_maquinas);
                $stmt_maquinas->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                $stmt_maquinas->execute();

                // Preparar la consulta para eliminar la ubicación
                $sql = "DELETE FROM ubicacion WHERE idubicacion = :idubicacion";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                $stmt->execute();
    
                // Redirigir después de eliminar
                header("Location: fabricacion.php"); // O la página que desees redirigir
                exit;
            }
            
            // Verificar si se presionó "Aplicar"
            if (isset($_POST['Aplicar'])) {
                // Recuperar datos del formulario enviados por POST
                $cliente = $_POST['cliente'];
                $calle = $_POST['calle'];
                $num_portal = $_POST['num_portal'];
                $cod_postal = $_POST['cod_postal'];
                $provincia = $_POST['provincia'];

                // Concatenar los valores para formar el valor de 'dir'
                $dir = "$calle;$num_portal;$cod_postal;$provincia";

                try {
                    // Actualizar datos en la base de datos
                    $sql_update = "UPDATE ubicacion SET cliente = :cliente, dir = :dir WHERE idubicacion = :idubicacion";
                
                    $stmt_update = $dbh->prepare($sql_update);
                    $stmt_update->bindParam(':cliente', $cliente, PDO::PARAM_STR);
                    $stmt_update->bindParam(':dir', $dir, PDO::PARAM_STR);
                    $stmt_update->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
                    $stmt_update->execute();

                    echo "<p>Ubicación actualizada correctamente.</p>";
                
                    // Redirigir después de eliminar
                    header("Location: fabricacion.php"); // O la página que desees redirigir
                    exit;
            
                } catch (Exception $e) {
                    echo "<p>Error al actualizar la ubicación: " . $e->getMessage() . "</p>";
                }
            }
        } else {
            // Si no se pasa un idubicacion
            echo "<p>Error: No se especificó una ubicación válida para eliminar.</p>";
        }
    } catch (Exception $e) {
        echo "<p>Error al eliminar la ubicación: " . $e->getMessage() . "</p>";
    }
}    
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Ubicaciones</title>
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <link rel="stylesheet" href="/topvending/m2/css/maquinas.css">
    <link rel="stylesheet" href="/topvending/m2/css/modificar_ubicaciones.css">
</head>

<body>
    <form id="formulario_maquina" method="POST" enctype="multipart/form-data">
        <table id="tablita">
            <thead>
                <tr id="encabezados">
                    <th>Id de Ubicación</th>
                    <th>Cliente</th>
                    <th>Calle</th>
                    <th>Número de Portal</th>
                    <th>Código Postal</th>
                    <th>Provincia</th>
                </tr>
            </thead>
            <tr>
                <td>
                    <!-- Campo oculto para el ID de la máquina -->
                    <input type="hidden" name="idubicacion" value="<?php echo $idubicacion; ?>">
                    <?php echo $idubicacion; ?> <!-- Muestra el ID de la máquina en la celda -->
                </td>
                <td>
                    <input id="cliente" type="text" name="cliente" value="<?php echo $cliente; ?>">
                </td>
                <td>
                    <input id="calle" type="text" name="calle" value="<?php echo $calle; ?>">
                </td>
                <td>
                    <input id="num_portal" type="text" name="num_portal" value="<?php echo $num_portal; ?>">
                </td>
                <td>
                    <input id="cod_postal" type="text" name="cod_postal" value="<?php echo $cod_postal; ?>">
                </td>
                <td>
                    <input id="provincia" type="text" name="provincia" value="<?php echo $provincia; ?>">
                </td>
            </tr>
        </table>
        <input type="submit" name="Aplicar" value="Aplicar" id="aplicando">
        <button type="submit" name="BorrarMaquina" id="botonBorrar">Eliminar</button>
    </form>
</body>

</html>