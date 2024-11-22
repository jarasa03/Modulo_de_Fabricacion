<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();

// Sanitización del menú generado dinámicamente
echo crearMenu($dbh);
?>

<?php
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recibir y sanitizar los datos del formulario
        $cliente = htmlspecialchars($_POST['cliente']);
        $calle = htmlspecialchars($_POST['calle']);
        $numportal = htmlspecialchars($_POST['numportal']);
        $codigoPostal = htmlspecialchars($_POST['codigo_postal']);
        $provincia = htmlspecialchars($_POST['provincia']);

        // Validar que todos los campos estén completos
        if (!empty($calle) && !empty($numportal) && !empty($codigoPostal) && !empty($provincia) && !empty($cliente)) {
            // Formatear la dirección completa
            $direccion = "$calle;$numportal;$codigoPostal;$provincia";

            // Crear la consulta SQL
            $sql = "INSERT INTO ubicacion (cliente, dir) VALUES (:cliente, :dir)";

            // Preparar y ejecutar la consulta
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':cliente', $cliente);
            $stmt->bindParam(':dir', $direccion);

            try {
                $stmt->execute();
                echo "<script>console.log('Nueva ubicación insertada correctamente');</script>";
                header("Location: ./fabricacion.php"); // Redireccionar a otra página después de la inserción
                exit;
            } catch (PDOException $e) {
                echo "<p style='color: red;'>Error al insertar la ubicación: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Error: Todos los campos deben estar completos.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Ubicación</title>
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
    <link rel="stylesheet" href="/topvending/m2/css/maquinas.css">
    <link rel="stylesheet" href="/topvending/m2/css/modificar_ubicaciones.css">
    <link rel="stylesheet" href="/topvending/css/stylesheet_m2.css">
</head>
<body>
    <!-- Mantener el menú oculto -->
    <div style="display: none;"><?php echo $menu_sanitizado; ?></div>


    <form id="formulario_ubicacion" method="POST">
        <table id="tablita">
            <thead>
                <tr id="encabezados">
                    <th class="th_principal">Cliente</th>
                    <th class="th_principal">Calle</th>
                    <th class="th_principal">Número de Portal</th>
                    <th class="th_principal">Código Postal</th>
                    <th class="th_principal">Provincia</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input id="cliente" type="text" name="cliente" value="<?php echo htmlspecialchars($cliente ?? ''); ?>" required></td>
                    <td><input id="calle" type="text" name="calle" value="<?php echo htmlspecialchars($calle ?? ''); ?>" required></td>
                    <td><input id="numportal" min="1" max="1000" type="number" name="numportal" value="<?php echo htmlspecialchars($numportal ?? ''); ?>" required></td>
                    <td><input id="codigo_postal" min="1000" max="60000" type="number" name="codigo_postal" value="<?php echo htmlspecialchars($codigoPostal ?? ''); ?>" required></td>
                    <td><input id="provincia" type="text" name="provincia" value="<?php echo htmlspecialchars($provincia ?? ''); ?>" required></td>
                </tr>
            </tbody>
        </table>
        <div style="text-align: center;">
            <input type="submit" value="Aplicar" id="aplicando">
        </div>
    </form>
</body>
</html>
