<?php
session_start();
define("DOCROOT", $_SERVER['DOCUMENT_ROOT'] . "/topvending");
require_once DOCROOT . "/clases/basededatos.php";
require_once DOCROOT . '/clases/funciones.php';
$dbh = conectar(); // Establecemos la conexión con la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $numero_serie = $_POST['numero_serie'];
    $id_estado = $_POST['id_estado'];
    $id_ubicacion = $_POST['id_ubicacion'];
    $capacidad = $_POST['capacidad'];
    $stockmax = $_POST['stockmax'];
    $modelo = $_POST['modelo'];

    // Foto por defecto en caso de no subir ninguna
    $foto = 'default.jpg'; // Valor por defecto

    // Procesar la foto si se ha subido
    if (!empty($_FILES['foto']['name'])) {
        $uploads_dir = DOCROOT . '/resources/'; // Carpeta donde se guardarán las fotos
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true); // Crear la carpeta si no existe
        }

        $file_name = basename($_FILES['foto']['name']);
        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_path = $uploads_dir . $file_name;

        // Mover la foto subida a la carpeta
        if (move_uploaded_file($file_tmp, $file_path)) {
            $foto = $file_name; // Actualizar el nombre de la foto
        } else {
            echo "Error al subir la foto.";
        }
    }

    // Crear la consulta SQL
    $sql = "INSERT INTO maquina (
                numserie, idestado, idubicacion, capacidad, stockmax, modelo, foto
            ) VALUES (
                :numserie, :idestado, :idubicacion, :capacidad, :stockmax, :modelo, :foto
            )";

    // Preparar y ejecutar la consulta
    $stmt = $dbh->prepare($sql);

    $stmt->bindParam(':numserie', $numero_serie);
    $stmt->bindParam(':idestado', $id_estado);
    $stmt->bindParam(':idubicacion', $id_ubicacion);
    $stmt->bindParam(':capacidad', $capacidad);
    $stmt->bindParam(':stockmax', $stockmax);
    $stmt->bindParam(':modelo', $modelo);
    $stmt->bindParam(':foto', $foto);

    // Ejecutar la consulta
    $stmt->execute();

    echo "<script>console.log('Nueva máquina insertada correctamente');</script>";

    // Redireccionar a la página de fabricación
    header("Location: ./fabricacion.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertar Máquina</title>
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
    <form id="formulario_maquina" method="POST" enctype="multipart/form-data">
        <table>
            <tr>
                <th>Número de Serie</th>
                <td><input id="num_serie" type="text" name="numero_serie" required></td>
            </tr>
            <tr>
                <th>Id de Estado</th>
                <td><input id="id_estado" type="number" name="id_estado" required></td>
            </tr>
            <tr>
                <th>Id de Ubicación</th>
                <td><input id="id_ubi" type="number" name="id_ubicacion" required></td>
            </tr>
            <tr>
                <th>Capacidad</th>
                <td><input id="capacidad" type="number" name="capacidad" required></td>
            </tr>
            <tr>
                <th>Stock Máximo</th>
                <td><input id="stockmax" type="number" name="stockmax" required></td>
            </tr>
            <tr>
                <th>Modelo</th>
                <td><input id="modelo" type="text" name="modelo" required></td>
            </tr>
            <tr>
                <th>Foto</th>
                <td><input id="foto" type="file" name="foto"></td>
            </tr>
        </table>
        <input type="submit" value="Insertar" id="insertar">
    </form>
</body>
</html>