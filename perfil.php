<?php

include "php/conexion.php";
session_start();

if (!isset($_SESSION['IdUsuario'])) {
     header(header: "Location: login.php");
    exit();
}

$idUsuario = $_SESSION['IdUsuario'];

$sql_usuario = "SELECT NomUsuario, Foto FROM Usuarios WHERE IdUsuario = ?";
$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $idUsuario);
$stmt_usuario->execute();
$resultado_usuario = $stmt_usuario->get_result();

if ($resultado_usuario->num_rows === 0) {
    die("Usuario no encontrado.");
}
$datos_usuario = $resultado_usuario->fetch_assoc();
$nombreUsuario = htmlspecialchars($datos_usuario['NomUsuario']);
$fotoPerfil = htmlspecialchars($datos_usuario['Foto']);
$stmt_usuario->close();

//Consulta TODAS las fotos subidas por el usuario.
// Usa un JOIN para enlazar la tabla Fotos con Albumes y Usuarios.
$sql_fotos = "
    SELECT
        fotos.Titulo AS TituloFoto,
        fotos.Fichero,
        albumes.Titulo_album AS TituloAlbum
    FROM Fotos
    JOIN albumes ON fotos.Album = albumes.IdAlbum
    WHERE albumes.Usuario = ?
    ORDER BY fotos.FRegistro
";
$stmt_fotos = $conexion->prepare($sql_fotos);
$stmt_fotos->bind_param("i", $idUsuario);
$stmt_fotos->execute();
$resultado_fotos = $stmt_fotos->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo $nombreUsuario; ?></title>
    <link rel="stylesheet" href="css/perfil.css">
</head>
<body>
    <p><a href="index.php">← Volver al inicio</a> | <a href="php/logout.php">Cerrar sesión</a></p>
    <hr>
    
    <div class="perfil-info">
        <?php
        $ruta_perfil = !empty($fotoPerfil) ? "fotos_perfil/" . $fotoPerfil : "fotos_perfil/default_perfil.png";
        ?>
        <img src="<?php echo $ruta_perfil; ?>" alt="Foto de perfil de <?php echo $nombreUsuario; ?>">
        <div>
            <h1>Perfil de <?php echo $nombreUsuario; ?></h1>
            <p>Aquí puedes ver todas tus fotos.</p>
        </div>
    </div>

    <hr>

    <h3>Todas tus fotos</h3>
    <div class="galeria">
        <?php
        if ($resultado_fotos->num_rows > 0) {
            while ($foto = $resultado_fotos->fetch_assoc()) {
                $ruta_imagen = "fotos/" . htmlspecialchars($foto['Fichero']);
                $tituloFoto = htmlspecialchars($foto['TituloFoto']);
                $tituloAlbum = htmlspecialchars($foto['TituloAlbum']);
                ?>
                <div class="foto-item">
                    <img src="<?php echo $ruta_imagen; ?>" alt="<?php echo $tituloFoto; ?>">
                    <p><strong><?php echo $tituloFoto; ?></strong></p>
                    <small>Álbum: <?php echo $tituloAlbum; ?></small>
                </div>
                <?php
            }
        } else {
            echo "<p>Aún no has subido ninguna foto. <a href='crear_album.php'>¡Sube la primera!</a></p>";
        }

        $stmt_fotos->close();
        $conexion->close();
        ?>
    </div>

</body>
</html>