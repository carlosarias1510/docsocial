<?php
session_start();
include('includes/db.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contenido'])) {
    $contenido = $conn->real_escape_string($_POST['contenido']);
    $user_id = $_SESSION['user_id'];

    // Inserta el tweet en la base de datos
    $sql = "INSERT INTO tweets (user_id, contenido, fecha_publicacion) VALUES ('$user_id', '$contenido', NOW())";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php"); // Redirigir de nuevo al index después de publicar
        exit();
    } else {
        echo "Error al publicar el tweet: " . $conn->error;
    }
} else {
    echo "No se ha enviado ningún tweet.";
}
?>
