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

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los valores del formulario
    $idubicacion = $_POST['idubicacion'];
    $cliente = $_POST['cliente'];
    $calle = $_POST['calle'];
    $num_portal = $_POST['num_portal'];
    $cod_postal = $_POST['cod_postal'];
    $provincia = $_POST['provincia'];

    // Realizar la actualización en la base de datos
    try {
        $stmt = $dbh->prepare("UPDATE ubicacion SET cliente = :cliente, dir = :dir WHERE idubicacion = :idubicacion");
        
        // Formar la dirección completa (para guardarla en la base de datos)
        $dir_completa = $calle . ";" . $num_portal . ";" . $cod_postal . ";" . $provincia;
        
        $stmt->bindParam(':cliente', $cliente, PDO::PARAM_STR);
        $stmt->bindParam(':dir', $dir_completa, PDO::PARAM_STR);
        $stmt->bindParam(':idubicacion', $idubicacion, PDO::PARAM_INT);
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Mostrar mensaje de éxito
        echo "<script>console.log('Ubicación actualizada correctamente');</script>";
        
        header("Location: fabricacion.php");
        exit;
    } catch (PDOException $e) {
        // En caso de error, mostrar el mensaje
        echo "<script>console.log('Error al actualizar la ubicación: " . $e->getMessage() . "');</script>";
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
        <input type="submit" value="Aplicar" id="aplicando">
    </form>
</body>

</html>