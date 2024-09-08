<?php
session_start();
include('includes/db.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtiene la lista de amigos del usuario
$sql_amigos = "SELECT usuarios.id, usuarios.username 
                FROM seguidores 
                JOIN usuarios ON seguidores.seguido_id = usuarios.id
                WHERE seguidores.seguidor_id = '$user_id'";
$result_amigos = $conn->query($sql_amigos);

// Procesa el formulario de envío de mensaje
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['destinatario']) && isset($_POST['contenido'])) {
    $destinatario_id = $conn->real_escape_string($_POST['destinatario']);
    $contenido = $conn->real_escape_string($_POST['contenido']);

    // Inserta el mensaje en la base de datos
    $sql = "INSERT INTO mensajes (remitente_id, destinatario_id, contenido, fecha_envio) 
            VALUES ('$user_id', '$destinatario_id', '$contenido', NOW())";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: mensajes.php"); // Redirige a la página de mensajes después de enviar
        exit();
    } else {
        echo "Error al enviar el mensaje: " . $conn->error;
    }
}

// Obtiene los mensajes recibidos
$sql_mensajes = "SELECT mensajes.*, usuarios.username AS remitente_username 
                  FROM mensajes 
                  JOIN usuarios ON mensajes.remitente_id = usuarios.id
                  WHERE mensajes.destinatario_id = '$user_id'
                  ORDER BY mensajes.fecha_envio DESC";
$result_mensajes = $conn->query($sql_mensajes);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mensajes - DocSocial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container mt-5">
        <h2>Mensajes</h2>

        <!-- Formulario para enviar un nuevo mensaje -->
        <div class="mt-4">
            <h4>Enviar un mensaje</h4>
            <form method="post" action="mensajes.php">
                <div class="mb-3">
                    <label for="destinatario" class="form-label">Destinatario</label>
                    <select class="form-select" id="destinatario" name="destinatario" required>
                        <option value="" disabled selected>Selecciona un amigo</option>
                        <?php while ($amigo = $result_amigos->fetch_assoc()): ?>
                            <option value="<?php echo $amigo['id']; ?>"><?php echo htmlspecialchars($amigo['username']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="contenido" class="form-label">Contenido</label>
                    <textarea class="form-control" id="contenido" name="contenido" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>

        <!-- Mostrar los mensajes recibidos -->
        <div class="mt-5">
            <h4>Mensajes Recibidos</h4>
            <?php if ($result_mensajes->num_rows > 0): ?>
                <?php while($mensaje = $result_mensajes->fetch_assoc()): ?>
                    <div class="mb-3 p-3 border rounded">
                        <strong>De: <?php echo htmlspecialchars($mensaje['remitente_username']); ?></strong>
                        <p><?php echo htmlspecialchars($mensaje['contenido']); ?></p>
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])); ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No tienes mensajes.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <!-- Enlace a Bootstrap JS y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
