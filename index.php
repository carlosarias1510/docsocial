<?php
session_start();
include('includes/db.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consulta para obtener la información del usuario (nombre y foto de perfil)
$sql_user = "SELECT username, foto_perfil FROM usuarios WHERE id = '$user_id'";
$result_user = $conn->query($sql_user);
if ($result_user->num_rows > 0) {
    $usuario = $result_user->fetch_assoc();
} else {
    echo "Error: Usuario no encontrado.";
}

// Consulta para obtener todos los tweets publicados por cualquier usuario
$sql_tweets = "SELECT tweets.*, usuarios.username, usuarios.foto_perfil, 
                (SELECT COUNT(*) FROM likes WHERE likes.tweet_id = tweets.id) AS likes_count,
                (SELECT COUNT(*) FROM likes WHERE likes.tweet_id = tweets.id AND likes.user_id = '$user_id') AS liked_by_user
               FROM tweets 
               JOIN usuarios ON tweets.user_id = usuarios.id 
               ORDER BY tweets.fecha_publicacion DESC";

$result_tweets = $conn->query($sql_tweets);

// Manejo de la actualización de la foto de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_perfil'])) {
    $foto_perfil = $_FILES['foto_perfil'];
    
    // Verifica si el archivo se subió correctamente
    if ($foto_perfil['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $upload_file = $upload_dir . basename($foto_perfil['name']);

        // Mueve el archivo al directorio de uploads
        if (move_uploaded_file($foto_perfil['tmp_name'], $upload_file)) {
            // Actualiza la ruta de la foto de perfil en la base de datos
            $sql_update_photo = "UPDATE usuarios SET foto_perfil='$upload_file' WHERE id='$user_id'";
            if ($conn->query($sql_update_photo) === TRUE) {
                header("Location: index.php"); // Redirige a la página actual para mostrar la nueva foto
                exit();
            } else {
                echo "Error al actualizar la foto de perfil: " . $conn->error;
            }
        } else {
            echo "Error al subir el archivo.";
        }
    } else {
        echo "Error en la carga del archivo.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - DocSocial</title>

    <!-- Enlace a Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.9.1/font/bootstrap-icons.css">

    <!-- Enlace a Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Enlace a tu archivo CSS personalizado -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <!-- Sección superior con la foto de perfil del usuario y formulario para modificarla -->
    <div class="container mt-5">
        <div class="d-flex align-items-center">
            <!-- Mostrar la foto de perfil del usuario -->
            <img src="<?php echo !empty($usuario['foto_perfil']) ? $usuario['foto_perfil'] : 'assets/images/perfil.jpg'; ?>" 
                 alt="Foto de perfil" class="img-fluid rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
            
            <h3>@<?php echo htmlspecialchars($usuario['username']); ?></h3>
        </div>

        <!-- Formulario para cambiar la foto de perfil -->
        <form method="post" action="index.php" enctype="multipart/form-data" class="mt-3">
            <div class="mb-3">
                <label for="foto_perfil" class="form-label">Cambiar Foto de Perfil</label>
                <input type="file" class="form-control" id="foto_perfil" name="foto_perfil">
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Foto</button>
        </form>

        <!-- Formulario para publicar un tweet -->
        <div class="mt-5">
            <h4>Publicar un nuevo tweet</h4>
            <form method="post" action="publicar_tweet.php">
                <textarea class="form-control mb-3" name="contenido" rows="3" maxlength="280" placeholder="¿Qué está pasando?"></textarea>
                <button type="submit" class="btn btn-primary">Publicar</button>
            </form>
        </div>

        <!-- Mostrar los tweets en el timeline -->
        <div class="mt-5">
            <h4>Timeline</h4>
            <?php if ($result_tweets->num_rows > 0): ?>
                <?php while($tweet = $result_tweets->fetch_assoc()): ?>
                    <div class="tweet d-flex align-items-start mb-3 border p-3 rounded">
                        <!-- Mostrar la foto de perfil del autor del tweet -->
                        <img src="<?php echo !empty($tweet['foto_perfil']) ? $tweet['foto_perfil'] : 'assets/images/perfil.jpg'; ?>" 
                             alt="Foto de perfil" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">

                        <!-- Contenido del tweet -->
                        <div>
                            <h6>@<?php echo htmlspecialchars($tweet['username']); ?></h6>
                            <p><?php echo htmlspecialchars($tweet['contenido']); ?></p>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($tweet['fecha_publicacion'])); ?></small>

                            <!-- Botón de Me gusta con icono de corazón -->
                            <form method="post" action="me_gusta.php" class="d-inline">
                                <input type="hidden" name="tweet_id" value="<?php echo $tweet['id']; ?>">
                                <button type="submit" name="me_gusta" class="btn btn-outline-<?php echo $tweet['liked_by_user'] ? 'danger' : 'primary'; ?> btn-sm">
                                    <!-- Icono de corazón -->
                                    <i class="bi bi-heart<?php echo $tweet['liked_by_user'] ? '-fill' : ''; ?>"></i> 
                                    (<?php echo $tweet['likes_count']; ?>)
                                </button>
                            </form>

                            <!-- Botón de eliminar tweet -->
                            <form method="post" action="eliminar_tweet.php" class="d-inline ms-2">
                                <input type="hidden" name="tweet_id" value="<?php echo $tweet['id']; ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay tweets para mostrar.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <!-- Enlace a Bootstrap JS y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
