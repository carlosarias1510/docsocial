<?php
session_start();
include('includes/db.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener lista de amigos
$sql_friends = "SELECT u.id, u.username FROM seguidores s
                JOIN usuarios u ON s.seguido_id = u.id
                WHERE s.seguidor_id = '$user_id'";
$result_friends = $conn->query($sql_friends);

// Enviar el mensaje
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amigo_id']) && isset($_POST['mensaje'])) {
    $amigo_id = $conn->real_escape_string($_POST['amigo_id']);
    $mensaje = $conn->real_escape_string($_POST['mensaje']);

    // Insertar el mensaje en la base de datos
    $sql_message = "INSERT INTO mensajes (emisor_id, receptor_id, contenido, fecha_envio) 
                    VALUES ('$user_id', '$amigo_id', '$mensaje', NOW())";
    
    if ($conn->query($sql_message) === TRUE) {
        header("Location: mensajes.php"); // Redirigir a la página de mensajes
        exit();
    } else {
        echo "Error al enviar el mensaje: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviar Mensaje - DocSocial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container mt-5">
        <h2>Enviar Mensaje</h2>
        <form method="post" action="enviar_mensaje.php">
            <div class="mb-3">
                <label for="amigo" class="form-label">Seleccionar Amigo</label>
                <select class="form-select" id="amigo" name="amigo_id" required>
                    <option value="">Selecciona un amigo</option>
                    <?php while ($friend = $result_friends->fetch_assoc()): ?>
                        <option value="<?php echo $friend['id']; ?>"><?php echo htmlspecialchars($friend['username']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="mensaje" class="form-label">Mensaje</label>
                <textarea class="form-control" id="mensaje" name="mensaje" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
