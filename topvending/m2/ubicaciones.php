<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();
echo crearMenu($dbh);
?>

<?php
try {
    // Consultar máquinas con idubicacion = 1
    $sql = "
        SELECT m.numserie, u.dir 
        FROM maquina m 
        JOIN ubicacion u ON m.idubicacion = u.idubicacion 
        WHERE u.idubicacion = 1";
    $stmt = $dbh->query($sql);
    $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar la solicitud POST para actualizar la ubicación
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $numserie = $_POST['numserie'];
        $calle = $_POST['calle'];
        $numportal = $_POST['numportal'];
        $codigoPostal = $_POST['codigo_postal'];
        $provincia = $_POST['provincia'];
        $cliente = $_POST['cliente']; // Campo cliente

        // Validar que todos los campos estén completos
        if (!empty($calle) && !empty($numportal) && !empty($codigoPostal) && !empty($provincia) && !empty($cliente)) {
            // Formatear la nueva dirección
            $nuevaDireccion = "$calle;$numportal;$codigoPostal;$provincia";

            try {
                // Iniciar una transacción
                $dbh->beginTransaction();

                // Insertar la nueva ubicación con el cliente en la tabla 'ubicacion'
                $insertSqlUbicacion = "INSERT INTO ubicacion (dir, cliente) VALUES (:nuevaDireccion, :cliente)";
                $insertStmtUbicacion = $dbh->prepare($insertSqlUbicacion);
                $insertStmtUbicacion->bindParam(':nuevaDireccion', $nuevaDireccion);
                $insertStmtUbicacion->bindParam(':cliente', $cliente);
                $insertStmtUbicacion->execute();

                // Obtener el idubicacion generado
                $nuevoIdUbicacion = $dbh->lastInsertId();

                // Actualizar el idubicacion de la máquina seleccionada
                $updateSqlMaquina = "UPDATE maquina SET idubicacion = :nuevoIdUbicacion WHERE numserie = :numserie";
                $updateStmtMaquina = $dbh->prepare($updateSqlMaquina);
                $updateStmtMaquina->bindParam(':nuevoIdUbicacion', $nuevoIdUbicacion);
                $updateStmtMaquina->bindParam(':numserie', $numserie);
                $updateStmtMaquina->execute();

                // Confirmar la transacción
                $dbh->commit();

                // Redirigir para actualizar la lista de máquinas
                header("Location: ubicaciones.php");
                exit;
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                $dbh->rollBack();
                echo "<p style='color: red;'>Error al actualizar la ubicación: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Error: Todos los campos deben estar completos.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
try {
    // Consultar máquinas con idubicacion = 1

// Obtener ubicaciones existentes (excepto el taller)
$sqlUbicaciones = "SELECT idubicacion, dir, cliente FROM ubicacion WHERE idubicacion != 1";
$stmtUbicaciones = $dbh->query($sqlUbicaciones);
$ubicaciones = $stmtUbicaciones->fetchAll(PDO::FETCH_ASSOC);

// Procesar la solicitud POST para asignar una ubicación existente
// Procesar la solicitud POST para asignar una ubicación existente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asignarUbicacion'], $_POST['numserie'], $_POST['idubicacion'])) {
    $numserie = $_POST['numserie'];
    $idubicacion = $_POST['idubicacion'];

    try {
        $updateSql = "UPDATE maquina SET idubicacion = :idubicacion WHERE numserie = :numserie";
        $stmt = $dbh->prepare($updateSql);
        $stmt->bindParam(':idubicacion', $idubicacion);
        $stmt->bindParam(':numserie', $numserie);
        $stmt->execute();

        // Redirigir para actualizar la lista de máquinas
        header("Location: ubicaciones.php");
        exit;
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error al asignar la ubicación: " . $e->getMessage() . "</p>";
    }
}


} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actualizar Ubicacion y Cliente</title>
    <link rel="stylesheet" href="./css/ubicaciones.css">
    <link rel="stylesheet" href="/topvending/css/hallentrada.css">
</head>

<body>
    <h1 id="h1">Asignar maquinas en el taller a una nueva Ubicacion</h1>

    <?php if ($maquinas): ?>
        <table id="tabla1">
            <thead>
                <tr>
                    <th>Número de Serie</th>
                    <th>Calle</th>
                    <th>Número de Portal</th>
                    <th>Código Postal</th>
                    <th>Provincia</th>
                    <th>Cliente</th>
                    <th>Accion</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($maquinas as $maquina): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($maquina['numserie']); ?></td>
                        <form method="POST" action="ubicaciones.php">
                            <td><input type="text" name="calle" pattern="[A-Za-z\s]+" required></td>
                            <td><input type="number" name="numportal" min="1" required></td>
                            <td><input type="number" name="codigo_postal" min="1000" max="60000"required></td>
                            <td><input type="text" name="provincia" pattern="[A-Za-z\s]+" required></td>
                            <td><input type="text" name="cliente" required></td>
                            <td>
                                <input type="hidden" name="numserie" value="<?php echo htmlspecialchars($maquina['numserie']); ?>">
                                <button type="submit" id="btn1">Confirmar</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay máquinas en el taller para actualizar.</p>
    <?php endif; ?>

    <h2 id="h2">Asignar Maquinas en el taller a Ubicaciones Existentes</h>

<?php if ($maquinas && $ubicaciones): ?>
    <table id="tabla2">
        <thead>
            <tr>
                <th>Número de Serie</th>
                <th>Ubicación Actual</th>
                <th>Nueva Ubicación</th>
                <th>Cliente</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($maquinas as $maquina): ?>
                <tr>
                    <td><?php echo htmlspecialchars($maquina['numserie']); ?></td>
                    <td><?php echo htmlspecialchars($maquina['dir']); ?></td>
                    <form method="POST" action="ubicaciones.php">
                        <td>
                            <select name="idubicacion" required>
                                <?php foreach ($ubicaciones as $ubicacion): ?>
                                    <option value="<?php echo htmlspecialchars($ubicacion['idubicacion']); ?>">
                                        <?php echo htmlspecialchars($ubicacion['dir'] . ' (' . $ubicacion['cliente'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <?php 
                                $ubicacionActual = array_filter($ubicaciones, fn($u) => $u['idubicacion'] == $maquina['dir']);
                                echo htmlspecialchars($ubicacionActual[0]['cliente'] ?? "N/A"); 
                            ?>
                        </td>
                        <td>
                            <input type="hidden" name="numserie" value="<?php echo htmlspecialchars($maquina['numserie']); ?>">
                            <button type="submit" name="asignarUbicacion" id="btn2">Asignar</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay máquinas en el taller o ubicaciones disponibles para asignar.</p>
<?php endif; ?>

</body>

</html>