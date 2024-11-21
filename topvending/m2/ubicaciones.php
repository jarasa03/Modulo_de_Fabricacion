<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar();
echo crearMenu($dbh);

try {
    // Obtener las máquinas en el taller
    $sql = "
        SELECT m.numserie 
        FROM maquina m 
        JOIN ubicacion u ON m.idubicacion = u.idubicacion 
        WHERE u.idubicacion = 1";
    $stmt = $dbh->query($sql);
    $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener ubicaciones existentes (excepto el taller)
    $sqlUbicaciones = "SELECT idubicacion, dir FROM ubicacion WHERE idubicacion != 1";
    $stmtUbicaciones = $dbh->query($sqlUbicaciones);
    $ubicaciones = $stmtUbicaciones->fetchAll(PDO::FETCH_ASSOC);

    // Procesar solicitud POST para asignar ubicación
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asignarUbicacion'], $_POST['numserie'], $_POST['idubicacion'])) {
        $numserie = $_POST['numserie'];
        $idubicacion = $_POST['idubicacion'];

        try {
            $updateSql = "UPDATE maquina SET idubicacion = :idubicacion WHERE numserie = :numserie";
            $stmt = $dbh->prepare($updateSql);
            $stmt->bindParam(':idubicacion', $idubicacion);
            $stmt->bindParam(':numserie', $numserie);
            $stmt->execute();

            // Redirigir para reflejar cambios
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
    <title>Actualizar Ubicación</title>
    <link rel="stylesheet" href="./css/ubicaciones.css">
    <link rel="stylesheet" href="/topvending/css/stylesheet_m2.css">
</head>
<body>
    <h1 id="h1">Asignar Máquinas en el Taller a Ubicaciones Existentes</h1>

    <?php if ($maquinas && $ubicaciones): ?>
        <table id="tablas">
            <thead>
                <tr>
                    <th class="th_principal">Número de Serie</th>
                    <th class="th_principal">Nueva Ubicación</th>
                    <th class="th_principal">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($maquinas as $maquina): ?>
                    <tr>
                        <td class="tabla_principal_td"><?php echo htmlspecialchars($maquina['numserie']); ?></td>
                        <form method="POST" action="ubicaciones.php">
                            <td class="tabla_principal_td">
                                <select name="idubicacion" required>
                                    <?php foreach ($ubicaciones as $ubicacion): ?>
                                        <option value="<?php echo htmlspecialchars($ubicacion['idubicacion']); ?>">
                                            <?php echo htmlspecialchars($ubicacion['dir']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="tabla_principal_td">
                                <input type="hidden" name="numserie" value="<?php echo htmlspecialchars($maquina['numserie']); ?>">
                                <button type="submit" name="asignarUbicacion" id="btn1">Asignar</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="error">No hay máquinas en el taller o ubicaciones disponibles para asignar.</p>
    <?php endif; ?>
</body>
</html>
