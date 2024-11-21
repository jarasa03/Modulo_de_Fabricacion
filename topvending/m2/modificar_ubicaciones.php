<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();
echo crearMenu($dbh);

// Recuperar las cookies individuales si están definidas
$idubicacion = isset($_COOKIE['idubicacion']) ? htmlspecialchars($_COOKIE['idubicacion']) : '';
$numero_serie = isset($_COOKIE['cliente']) ? htmlspecialchars($_COOKIE['cliente']) : '';
$id_estado = isset($_COOKIE['dir']) ? htmlspecialchars($_COOKIE['dir']) : '';
echo "<script>console.log('" . $idubicacion . "')</script>"
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Ubicaciones</title>
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
</head>

<body>
    <form id="formulario_maquina" method="POST" enctype="multipart/form-data">
        <table>
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
                    <select id="id_estado" name="id_estado">
                        <option value="1" <?php echo ($id_estado == "1") ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo ($id_estado == "2") ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo ($id_estado == "3") ? 'selected' : ''; ?>>3</option>
                    </select>
                </td>
                <td>
                    <select id="id_ubicacion" name="id_ubicacion">
                        <?php
                        $ubicaciones_query = "SELECT DISTINCT idubicacion FROM ubicacion";
                        $stm = $dbh->prepare($ubicaciones_query);
                        $stm->execute();
                        $ubicaciones = $stm->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($ubicaciones as $ubicacion_option) {
                            $selected = ($id_ubicacion == $ubicacion_option) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($ubicacion_option) . "' $selected>" . htmlspecialchars($ubicacion_option) . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <select id="modelo" name="modelo">
                        <?php
                        $modelos_query = "SELECT DISTINCT modelo FROM maquina";
                        $stm = $dbh->prepare($modelos_query);
                        $stm->execute();
                        $modelos = $stm->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($modelos as $modelo_option) {
                            $selected = ($modelo == $modelo_option) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($modelo_option) . "' $selected>" . htmlspecialchars($modelo_option) . "</option>";
                        }
                        
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <input type="submit" value="Aplicar" id="aplicar">
    </form>
    
</body>

</html>