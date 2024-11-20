<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar(); // Establecemos la conexión con la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $cliente = $_POST['cliente'];
    $direccion = $_POST['direccion'];

    // Crear la consulta SQL
    $sql = "INSERT INTO ubicacion (
                cliente, dir
            ) VALUES (
                :cliente, :dir
            )";

    // Preparar y ejecutar la consulta
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':cliente', $cliente);
    $stmt->bindParam(':dir', $direccion);

    // Ejecutar la consulta
    try {
        $stmt->execute();
        echo "<script>console.log('Nueva ubicación insertada correctamente');</script>";
        header("Location: ./fabricacion.php"); // Redireccionar a otra página después de la inserción
        exit;
    } catch (PDOException $e) {
        echo "Error al insertar la ubicación: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertar Ubicación</title>
    <link rel="stylesheet" href="./css/maquinas.css">
    <script src="./js/maquinas.js" defer></script>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
            margin: 50px auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        td input {
            width: 90%;
            padding: 5px;
            text-align: center;
        }
        #insertar {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        #insertar:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <button onclick="window.location.href='../../login.php'">Cerrar sesión</button>
    </header>
    <form id="formulario_ubicacion" method="POST">
        <table>
            <tr>
                <th>Cliente</th>
                <td><input id="cliente" type="text" name="cliente" required></td>
            </tr>
            <tr>
                <th>Dirección</th>
                <td><input id="direccion" type="text" name="direccion" required></td>
            </tr>
        </table>
        <input type="submit" value="Insertar" id="insertar">
    </form>
</body>
</html>
