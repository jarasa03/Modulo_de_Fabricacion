<?php
session_start();
require_once "../../clases/basededatos.php";
require_once "../../clases/funciones.php";
$dbh = conectar();
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Ubicacion y Cliente</title>
    <link rel="stylesheet" href="./css/maquinas.css">
</head>
<body>
    <h1>Mostrando maquinas en el taller que faltan por asignar </h1>

    <?php if ($maquinas): ?>
        <table>
            <thead>
                <tr>
                    <th>Número de Serie</th>
                    <th>Calle</th>
                    <th>Número de Portal</th>
                    <th>Código Postal</th>
                    <th>Provincia</th>
                    <th>Cliente</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($maquinas as $maquina): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($maquina['numserie']); ?></td>
                        <form method="POST" action="ubicaciones.php">
                            <td><input type="text" name="calle" required></td>
                            <td><input type="number" name="numportal" required></td>
                            <td><input type="number" name="codigo_postal" required></td>
                            <td><input type="text" name="provincia" required></td>
                            <td><input type="text" name="cliente" required></td>
                            <td>
                                <input type="hidden" name="numserie" value="<?php echo htmlspecialchars($maquina['numserie']); ?>">
                                <button type="submit">Confirmar</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay máquinas en el taller para actualizar.</p>
    <?php endif; ?>
</body>
</html>