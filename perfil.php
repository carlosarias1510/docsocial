<?php
// perfil.php

session_start();
include('includes/db.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consulta para obtener la información del perfil del usuario
$sql = "SELECT username, nombre, apellido, descripcion,
               (SELECT COUNT(*) FROM seguidores WHERE seguido_id = '$user_id') AS num_seguidores,
               (SELECT COUNT(*) FROM seguidores WHERE seguidor_id = '$user_id') AS num_seguidos
        FROM usuarios WHERE id = '$user_id'";
        
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
} else {
    echo "Error: Usuario no encontrado.";
}

// Procesar el formulario de actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellido = $conn->real_escape_string($_POST['apellido']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);

    // Actualizar los datos en la base de datos
    $sql_update = "UPDATE usuarios SET nombre='$nombre', apellido='$apellido', descripcion='$descripcion' WHERE id='$user_id'";
    
    if ($conn->query($sql_update) === TRUE) {
        // Redirigir a la página de perfil para evitar reenvíos del formulario
        header("Location: perfil.php");
        exit();
    } else {
        echo "Error al actualizar el perfil: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuario - DocSocial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <!-- Información de Seguidores y Seguidos -->
                <div class="d-flex justify-content-around">
                    <p><strong><?php echo $usuario['num_seguidores']; ?></strong> Seguidores</p>
                    <p><strong><?php echo $usuario['num_seguidos']; ?></strong> Seguidos</p>
                </div>
            </div>

            <div class="col-md-8">
                <h2>@<?php echo htmlspecialchars($usuario['username']); ?></h2>
                <p><strong><?php echo htmlspecialchars($usuario['nombre']) . " " . htmlspecialchars($usuario['apellido']); ?></strong></p>
                <p><?php echo htmlspecialchars($usuario['descripcion']); ?></p>

                <!-- Formulario para modificar los datos -->
                <form method="post" action="perfil.php">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($usuario['descripcion']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <!-- Enlace a Bootstrap JS y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
